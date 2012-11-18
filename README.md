# CbApi - A simple PHP API to manage HTTP protocol intricacies

CbApi allows you to build REST web services without caring too much about
parsing or setting HTTP headers, HTTP response codes or caching. It separates
the generation of content from its formatting. You can, for example format the
same content in either JSON or HTML by specifying two formatters. The right one
will be chosen after content negotiation and this is totally transparent to the
class that generates the content.

CbApi allowes you to specify your own authorization provider which for each
request will check if it's allowed and produce a useful HTTP response code if
not.

CbApi doesn't route requests to handlers. That's the job of the CbRewriter
project. It does include a class to do browser feature detection. You shouldn't
use that and rely either on responsive design or detect features and decide what
to do with them in the browser with Javascript.

## Providers

CbApi provides three classes that can be used to organize content generation and
formatting:

1. CbResourceProvider is the one you probably want to use. It chooses a resource
handler from the ones you specify by examining the "resource" property of the
previously parsed request URI. It then executes a method corresponding to the
HTTP request method on that handler if
 * the authorization provider allows it and
 * the cache provider doesn't tell it to deliver no content and
 * the chosen formatter actually requests the data or the HTTP method is unsafe.

2. CbRpcProvider works roughly like CbResourceProvider, but selects its handlers
by a "method" property which can be given via either URI, POST, GET or COOKIE
parameters. It doesn't need a "resource" property and doesn't care about HTTP
request methods. This class can be chosen if you're implementing an application
which needs a serverside state. For example, a user registration may need to
associate different requests with each other and certain methods are allowed to
be called only in the context constructed by certain previous method calls.

3. CbTemplateProvider is meant to deliver easily cacheable templates for CbUi
windows. Those shouldn't contain any real content, but only HTML structure.

## Handlers

A handler is a class that implements the action to be executed for a request.
Depending on which provider you're using you'll want to implement different
interfaces for the handler. The most common one will be
CbResourceHandlerInterface. There you have to provide get, put, post and delete
methods. Alternately you can implement CbRpcHandlerInterface for RPC interfaces.
There you only have one method "handle". You should return something that can
be used by all available formatters from this method. It's advisable to return
a nested array. You can serialize a Propel object hierarchy into a nested array
with CbSerialize.

Also you can and should implement a method "meta" which will inform the provider
about various properties of the resource or method being interacted with.
Currently those are:
 * max\_age, last\_modified, privacy, etag, etag_weak for the respective cache
   properties.
 * vary for specifying additional header properties by which the content may
   vary. The provider already knows that the content will vary by "accept".
 * formats for overriding the choice of formatters for the generated content.
   For example you can state array("text/html" => "MySpecialFormatter") to
   instruct the provider to format the content with MySpecialFormatter if it is
   to be formatted in HTML.
 * name for stating a name which may show up in meta data for the generated
   content. The default HTML formatter uses this to generate a <title> tag.

You can always throw a CbApiException if the method is not available or any
other problems arise. Specify the appropriate HTTP response code as the first
argument to its constructor then.

## Example

The preferred way to use it can be demonstrated by the following example:

    $provider = new CbResourceProvider(array(
       'page' => 'PageHandler',
    ), array(
       'auth_provider' => 'AuthHandler'
    ));
    $provider->handle();

This specifies one resource, "page" which will be handles by the class
PageHandler. Additionally you specify a class AuthHandler to check
authorization. The following PageHandler, given some Propel active record class
named "Page", implements a simple system of hierarchical pages:

    class PageHandler implements CbResourceHandlerInterface {

       public function __construct($config) {
          parent::__construct($config);
       }

       public function meta() {
          return array(
             'max_age' => 86400,
             'formats' => array(
                'text/html' => 'PageHtmlFormatter'
             )
          );
       }

       private function setParent($page, $params, $name)
       {
          if (isset($params[$name])) {
             $page->setIdParent($params[$name] === 'null' ? null : intval($params[$name]));
          }
       }


       private function serialize($page)
       {
          $s = new CbPropelSerializer();
          return CbPropelSerializer::objectToArray($page, array(
             "Id", "Type", "Label", "Description", "Visible", "InMenu",
             "UrlName" => $s->rename('Name'),
             "NumChildren" => $s->with_args(false)
          ));
       }

       private function save($page, $params)
       {
          try {
             if (isset($params['type'])) $page->setType($params['type']);
             if (isset($params['label'])) $page->setLabel($params['label']);
             [...]
             $page->save();
             return $this->serialize($page);
          } catch (PropelException $e) {
             throw new CbApiException(500, "Unknown database error");
          }
          return false;
       }

       public function delete(array $params)
       {
          return (PageQuery::create()->filterById($params['id'])->delete() > 0);
       }

       public function get(array $params)
       {
          $children = array();
          foreach(PageQuery::create()
                ->orderByPosition()
                ->findByIdParent(isset($params['id']) ? $params['id'] : null) as $page) {
             $children[] = $this->serialize($page);
          };
          return $children;
       }

       public function post(array $params)
       {
          $child = new Page();
          $this->setParent($child, $params, 'id');
          return $this->save($child, $params);
       }

       public function put(array $params)
       {
          $child = PageQuery::create()->findPk($params['id']);
          if ($child) {
             if (isset($params['id_parent'])) {
                if (($params['id_parent'] === 'null' &&
                      $child->getIdParent() !== null) ||
                      $params['id_parent'] !== 'null' &&
                      $child->getIdParent() !== intval($params['id_parent'])) {
                   $child->shiftSiblings(false);
                }
                $this->setParent($child, $params, 'id_parent');
             }
             return $this->save($child, $params);
          } else {
             throw new CbApiException(404, "There is no page with ID ".$params['id']);
             return false;
          }
       }
    }