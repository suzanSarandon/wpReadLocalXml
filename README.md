# wpReadLocalXml
A Wordpress plugin to read Local xml file, present the contents and notify admin if products count is 30% off.


# Images
![](https://github.com/suzanSarandon/wpReadLocalXml/blob/master/images/xmlPluginDisplay.JPG)

![](https://github.com/suzanSarandon/wpReadLocalXml/blob/master/images/xmlPluginOptions.JPG)

# Installation

Just create a folder in plugins directory within Wordpress and make sure it has the same name as the main php file of the plugin (in this case: 'my_plugin')
The locally placed xml file should be in wp-content/uploads/feed directory and by the name of 'skroutz.xml'.
The plugin will read the nodes of the xml file and output in the admin area the amount of 'product' nodes and simultaneously store this information along with the datetime that the readXml action occured.

The options tab of the plugin is to choose whether you want to perform the countXml action automatically or manually. By default is set to manually.
