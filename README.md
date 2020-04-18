### Custom Product Builder for Magento2 by Buildateam

Custom Product Builder is a unique tool that combines both the functionality for product customization 'Build Your Own Product' and product personalization (Add Monograms & Art) in one easy-to-use app with real-time product preview.

Our product configurator stacks transparent PNG layers on top of each other creating a complete product preview and allows changing components of a product offering a photorealistic preview of the product in real-time.

In addition to configuring the product details itself, the product builder also offers an ability to overlay areas on the product with uploadable logos/artwork/images and text/monograms.

## Available Product Customizations:
- Text Thumbnails: Product options listed as text thumbnails. (i.e: Sizes)
- Color Thumbnails: Product options listed as HEX color thumbnails. (i.e: Colors)
- Canvas Recoloring : Recolor product component layers uploaded in grayscale on the fly using HEX. (i.e: Colors)
- Image Thumbnails: Product options listed as image thumbnails. (i.e: Collar Type, Diamond Cut)
- Dropdown: Product options listed as a dropdown menu (i.e: Countries, Sizes)
- Image Upload: Allow customers upload and preview an image in a pre-defined position. (i.e: Logos, Artwork)
- Text Monogram Field: Allow customers to enter a short text w/ preview in a pre-defined position.
- Long Text Monogram Field: Allow customers to enter a long text w/ preview in a pre-defined position.
- Multiple Text Fields: Collect information from users without displaying it on the product. (i.e: Measurements)
- Quantity: Quantity box.
- Time & Date: Allow users to enter both date & time.
- Date: Allow users to enter date only.
- Time: Allow users to enter time only.
- Product Builder Features:
- Mobile Optimized
- Ability to add custom CSS to match the theme
- Ability to add custom JS to create new features and look
- Currency selector
- Multiple product views (Front, Back, Left, Right, Top, Bottom)
- Product customization summary tab
- Complex conditional logic to hide and display options based on selection
- Language Translation Support
- 'Next Step' Button
- Unlimited price variants in Shopify
- Multiple cart product previews

## Installation Instructions via Composer. 
1) Add new repository into composer.json where extension is located.
2) run `composer require buildateam/m2-custom-product-builder:dev-master`
3) run `php bin/magento setup:upgrade`
4) run `php bin/magento setupe:di:compile`
5) run `rm -rf pub/static`
6) run `rm -rf var/view_preprocessed`
7) run `php bin/magento setup:static-content:deploy` or `bin/magento setup:deploy static-content en_GB en_US` (for multistore setup) 




## Products Import/Export
1) Export ```
curl -k -X GET https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/1 > ~/Downloads/prod1.json
```
2) Import ```
curl -X POST --data @<(cat 3.json) http://cpb.loc2/customproductbuilder/product/import/id/3
```

https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/3
https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/2
https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/1
