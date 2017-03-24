# Simplify 4.5 Evo

Simplify is website engine. It is simple to use and very useful for all projects.
This engine includes many extensions (called modules). Everybody can produce his own module for this engine.

Package modules for Simplify are: cp (control panel), htmlForms (create HTML-forms by PHP arrays), catalog (categories and pages),
finance (personal finance statistics), flatpickr (powerful js-calendar), geocoder (get coordinates of every object by its name - Yandex Tech),
paging (pagination for website and control panel), storage (images uploads/control), tinymce (powerful js-editor), yamap (Yandex map), realty 
(module to create website about realty with its own catalog, agents list).

## How can I create website with Simplify?

The simpliest way is creating module with routes.
To create module, create path module_name/module_name.php at the modules folder and then clear cache (if it turn on).

Then you should create module description array:

$simplify->init('module_name', array (
  'routing' => array ( // array of routes with regular expression => route name elements
    '/first_page.html/i' => 'first_page'
  )
));

Then you should create module class:

class module_name {
  public function routing($route) { // $route - a value from description array
    global $simplify;
    
    if ($route == 'first_page') {
      $simplify->title = 'First page title';
      return 'Hello world!';
    }   
}

That all!
