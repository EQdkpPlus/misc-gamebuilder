# misc-gamebuilder
Gamebuilder

Pageobject for EQdkp Plus, which allows creating the basic structure of games using a GUI

## Usage
* Put the file `gamebuilder_pageobject.class.php` into the folder `core/pageobjects/` of a EQdkp Plus installation
* Put the file `gamebuilder.html` into the folder `templates/base_template/` of the same EQdkp Plus installation
* Open the file `core/routing.class.php` and extend the `$arrStaticRoutes` array (Line 30) by adding `'gamebuilder' 	=> 'gamebuilder',` into the array.
```
		private $arrStaticRoutes = array(
			'settings'		=> 'settings',
			'login'			=> 'login',
			'mycharacters'	=> 'mycharacters',
			'search'		=> 'search',
			[...]
			'gamebuilder' 	=> 'gamebuilder',
		);
```
* Replace our URls, e.g. in <https://github.com/EQdkpPlus/misc-gamebuilder/blob/main/gamebuilder_pageobject.class.php#L49>, with your URL of the Gamebuilder
