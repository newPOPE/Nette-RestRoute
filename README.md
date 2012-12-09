# REST route for [Nette Framework](http://nette.org)

Route automatically maps CRUD to Presenters and actions in the defined module.
And creates parameters which are accessible in Presenter.  

- format
- id (autodetected)
- associations (an array with associations)
- data (raw data from the request)
- query (an array of items from the query string)


## Usage:

```
// $router is an instance of Nette\Application\Routers\RouteList  
$router[] = new RestRoute(
	'Api', // ApiModule
	array('json') // allowed formats
);
```

First parameter is a name of the module where the route will sends an Request. URL prefix will be generated. See examples.
####Examples:
 
```
'Api' => /api/<generated presenter name>
'My:Api' => /my/api/<generated presenter name>
...
```

Second parameter is an array with allowed formats.

## Examples

### Basic:
**URL:** ```/api/users.json``` &rarr; ```\ApiModule\UsersPresenter::read```  
**Method:** GET  
**Request body:** Empty  
**Params:**  

```
format = json
associations = array(0)
data = ""
query = array(0)
```
---
### Resource ID
**URL:** ```/api/users/123.json``` &rarr; ```\ApiModule\UsersPresenter::read```  
**Method:** GET  
**Request body:** Empty  
**Params:**  

```
format = json
id = 123
associations = array(0)
data = ""
query = array(0)
```
---
### Query params:
**URL:** ```/api/users.json?foo=bar&page=1``` &rarr; ```\ApiModule\UsersPresenter::read```  
**Method:** GET  
**Request body:** Empty  
**Params:**  

```
format = json
associations = array(0)
data = ""
query = array(
	foo => "bar"
	page => 1
)
```
---
### Create:
**URL:** ```/api/users.json``` &rarr; ```\ApiModule\UsersPresenter::create```  
**Method:** POST  
**Request body:**  

```
{
	"foo": "bar",
	"nested": {
		"foo": "bar"	
	}
}
```
  
**Params:**  

```
format = json
associations = array(0)
data = {"foo": "bar", "nested": {"foo": "bar"}}
query = array(0)
```
---
### Update:
**URL:** ```/api/users/123.json``` &rarr; ```\ApiModule\UsersPresenter::update```  
**Method:** PUT  
**Request body:**  

```
{
	"foo": "bar",
	"nested": {
		"foo": "bar"	
	}
}
```
  
**Params:**  

```
format = json
id = 123
associations = array(0)
data = {"foo": "bar", "nested": {"foo": "bar"}}
query = array(0)
```
---
### Delete:
**URL:** ```/api/users.json``` &rarr; ```\ApiModule\UsersPresenter::delete```  
**Method:** DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
id = 123
associations = array(0)
data = ""
query = array(0)
```
---
### Associations:
Last item (pair) before .<format> is main resource. Everything what is before the last item are associations ([apigee.com](http://apigee.com/about/)).

**URL:** ```/api/users/1/comments.json``` &rarr; ```\ApiModule\CommentsPresenter::read|create|update|delete```  
**Method:** GET, POST, PUT, DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
associations = array(
	users => 1
)
data = ""
query = array(0)
```

**URL:** ```/users/123/comments/456.json``` &rarr; ```\ApiModule\CommentsPresenter::read|create|update|delete```  
**Method:** GET, POST, PUT, DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
id = 456
associations = array(
	users => 123
)
data = ""
query = array(0)
```

**URL:** ```/users/1/blogs/2/comments.json``` &rarr; ```\ApiModule\CommentsPresenter::read|create|update|delete```  
**Method:** GET, POST, PUT, DELETE  
**Request body:** Empty  
**Params:**  

```
format = json
id = 1
associations = array(
	users => 1
	blogs => 2
)
data = ""
query = array(0)
```

## TODO:
- Tests
- Fallbacks for HTTP methods PUT, DELETE (header, query param, …)



