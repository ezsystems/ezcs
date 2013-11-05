# Installation instructions

* Locate your PhpStorm home directory using the [PhpStorm official documentation](https://www.jetbrains.com/phpstorm/webhelp/project-and-ide-settings.html).
* Copy or create a symlink of the "eZ Publish.xml" file in config/codestyles/
* Restart PhpStorm.
* In PhpStorm, open File/Settings/Code Style
* Select the scheme : eZ Publish
* Click on "Apply"

# Activating PHP Code Sniffer

* Install phpcs and set up ezcs as default, as explained in [php/README.md](/php/README.md)
* In PhpStorm, open File/Settings
  * In PHP/Code Sniffer
    * Select your phpcs path
    * Click on "Apply"
  * In Inspections
    * Select PHP/PHP Code Sniffer Validation
      * On the right, in coding standard, click on the "refresh" button and select ezcs
      * Select the severity and the "show warning" option that you fits your taste. (For example : severity warning, show warning as typo)
    * Click on "Apply"


# Activating JSHint

* Install JSHint, as explained in [js/README.md](/js/README.md)
* In PhpStorm, open File/Settings/Javascript/Code Quality Tools/JSHint
* Click on enable
* Click on Use config files
* Point to : <path to your ezcs>/js/jshint.json
* Click on "Apply"
