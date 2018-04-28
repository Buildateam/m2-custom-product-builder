#Installation Instructions via Composer. 

1) Add new repository into composer.json wher located extension
2) run `composer require buildateam/m2-custom-product-builder:dev-master`
3) run `php bin:magento setup:upgrade`
4) run `php bin/magento setupe:di:compile`
4) run `php bin/magento setup:static-content:deploy`



#Curl Product Import

curl -k -X GET https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/1 > ~/Downloads/prod1.json

curl -X POST --data @<(cat 3.json) http://cpb.loc2/customproductbuilder/product/import/id/3

https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/3
https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/2
https://magento.thecustomproductbuilder.com/customproductbuilder/product/export/id/1