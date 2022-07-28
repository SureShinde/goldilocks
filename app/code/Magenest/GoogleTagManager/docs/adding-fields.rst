Adding ProductObject fields
===========================

**New in 4.0**

When adding new fields to the `productObject` (the product object visible in the dataLayer),
using one of the following observers is recommended.

- ``gtm_populate_product_object_from_product``
- ``gtm_populate_product_object_from_quote_item``
- ``gtm_populate_product_object_from_order_item``

The observer will receive the relevant object and fields may be added, modified or removed.

Simple example
--------------

.. code-block:: xml

    <!-- file: etc/frontend/events.xml -->
    <event name="gtm_populate_product_object_from_product">
        <observer instance="\Vaimo\MyModule\AddFoobar" name="..." />
    </event>

.. code-block:: php

    # file: AddFooBar.php
    class AddFooBar implements \Magento\Framework\Event\ObserverInterface
    {
        public function execute(\Magento\Framework\Event\Observer $observer)
        {
            // varies for products, quote and order items
            $product = $observer->getData('product');

            /** @var \Magento\Framework\DataObject $data */
            $data = $observer->getData('data');

            $data->setData('foo', 'bar');
        }
    }
