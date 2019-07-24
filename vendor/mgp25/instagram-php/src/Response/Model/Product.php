<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Product.
 *
 * @method string getCurrentPrice()
 * @method string getFullPrice()
 * @method bool getHasViewerSaved()
 * @method string getName()
 * @method string getPrice()
 * @method string getProductId()
 * @method bool isCurrentPrice()
 * @method bool isFullPrice()
 * @method bool isHasViewerSaved()
 * @method bool isName()
 * @method bool isPrice()
 * @method bool isProductId()
 * @method $this setCurrentPrice(string $value)
 * @method $this setFullPrice(string $value)
 * @method $this setHasViewerSaved(bool $value)
 * @method $this setName(string $value)
 * @method $this setPrice(string $value)
 * @method $this setProductId(string $value)
 * @method $this unsetCurrentPrice()
 * @method $this unsetFullPrice()
 * @method $this unsetHasViewerSaved()
 * @method $this unsetName()
 * @method $this unsetPrice()
 * @method $this unsetProductId()
 */
class Product extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name'             => 'string',
        'price'            => 'string',
        'current_price'    => 'string',
        'full_price'       => 'string',
        'product_id'       => 'string',
        'has_viewer_saved' => 'bool',
    ];
}
