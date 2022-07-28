Quick Start
===========

1. Navigate to: **Stores » Settings » Configuration » Analytics » Universal Analytics**
2. Each setting is detailed explained using the tooltips to the right.

Limitations
-----------

1. Virtual products and checkout funnel steps, since we only have a 
   payment step instead of shipping + payment.
2. Impressions data is currently only available when running this module 
   on Enterprise Edition
3. Impression clicks on category pages, related and up-sell products are 
   only available through Magento google tag manager. Which needs to be 
   activated separately or by using a project specific plugin.

Should you implement these clicks. Remove the `<script>` tag from 

.. code-block:: none
    vendor/vaimo/module-google-tag-manager/view/frontend/templates/enterprise/gtm.phtml

Since this functionality is already implemented in Magento google tag manager. 
