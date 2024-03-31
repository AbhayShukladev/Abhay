# CustomerDiscount By Abhay

#### Installation

```
+ Manual
```php
Step I      :Create a new folder named code (if it does not exist) under the app folder.
Step II     :Extract the CustomerDiscount-master.zip on your system and then Drag and Drop app/code/Abhay/CustomerDiscount.
Step III    :Now run the  following upgrade command in cmd


php bin/magento setup:upgrade

php bin/magento setup:di:compile

php bin/magento setup:static-content:deploy -f

php bin/magento cache:clean

php bin/magento cache:flush

