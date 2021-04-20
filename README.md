### Custom Product Builder for Magento 2 by Buildateam

Custom Product Builder is a unique tool that combines both the functionality for product customization 'Build Your Own Product' and product personalization (Add Monograms & Artwork) in one easy-to-use app with real-time product preview.

Our product configurator stacks transparent PNG layers on top of each other creating a complete product preview and allows changing components offering a photorealistic preview of the product in real-time.

In addition to configuring the product details itself, the product builder also offers an ability to overlay areas on the product with uploadable logos/artwork/images and text/monograms.

## Available Product Customizations:
(These are customizable options you can incorporate into your Custom Products to your clients)

 - Color Thumbnails: Product color options listed as HEX color thumbnails. Recolor product images on the fly into any color. (i.e fabric, texture, material, mask layers).

 - Image Upload: Allow customers to upload and preview images in pre-defined positions. (i.e: Logos, Artwork, Photos).

 - Text Monogram Field: Allow customers to enter text (including curved text) w/ live preview in pre-defined positions. Offer custom fonts or any of the pre-uploaded Google Fonts. Users can select font family, color, size. Single line or Multiple lines.

 - Printable Area: An interactive area for adding & moving around / rotating / scaling images & text. (i.e: T-Shirt Designer)

 - Image Thumbnails: Product options listed as image thumbnails. (i.e: Collar Type, Diamond Cut).

 - Text Thumbnails: Product options listed as text thumbnails. (i.e: Sizes, Pre-filled text templates).

 - Text Engraving For Wholesale: Allow wholesale customers add unique engraving per each item in the order. (i.e: Trophies, T-shirts, Gifts, Business Cards).

 - Wholesale Order / Size Breakdown: Split the total qty into multiple qty fields. Total qty will be calculated as a sum of several fields for entering the quantity. (i.e: Wholesale purchase order with different sizes, models, colors).

 - Quantity: An input field for choosing item quantity.

 - Quantity Breakdown: Offer price discounts based on the volume purchased. (For example 10-100: $1, 101-200: $0.5 etc.)

 - Deposit:  Set a deposit percentage amount of the total product price to be charged at the checkout. In this case, the total_price parameter with the final price will be added to the product details in the cart and the customer will pay only deposit at first.

 - Dropdown: Product options listed as a dropdown menu with manual entries (i.e: Countries, Sizes).

 - Dropdown Auto:  A dropdown of pre-generated number variants from a to b. Decimal or Fractions. (i.e: Measurements or Dimensions in inches or cm.)

 - Autocomplete Input Field: An input field with pre-generated allowed whole numbers from a to b. (i.e: Number of Portions)

 - Date & Time Picker: A date picker with YY - MM - DD  HH : MM options. (i.e: Pick Up Time)

 - Calculate Fields: The power of Open Office Calc at your fingerprints. Run complex calculations & formulas on backend to get the right price on the frontend. (i.e: Display Custom Product Price based on Width, Height or Length params.)

 - Bundle Builder Mode: Each selected option by the customer adds as a separate product to the cart. (i.e: Gift Basket).

 - Additional Price Field: An individual price field that is used for one time fees. (i.e: Leave Tips, Donate)


# Product Builder Features:
* Ability to add custom CSS to match the theme
* Ability to add custom JS to create new features
* Robust conditional logic rules offer an ability to show/hide options based on choices.
* Unlimited number of variants for your custom products bypassing the 100 variant limit.

# Important:
* Please read & watch the User Guide. It has a lot of useful tips.


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
