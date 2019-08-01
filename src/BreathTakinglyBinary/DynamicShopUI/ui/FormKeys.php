<?php
declare(strict_types=1);

namespace BreathTakinglyBinary\DynamicShopUI\ui;


interface FormKeys{

    // Values for use with form labels.
    const BACK = "back";

    const CAN_BUY = "can_buy";
    const CAN_SELL = "can_sell";

    const CATEGORY = "category";
    const CATEGORY_ADD = "add_categories";
    const CATEGORY_MODIFY = "modify_categories";
    const CATEGORY_NAME = "category_name";
    const CATEGORY_REMOVE = "category_remove";

    const IMG_LOCATION = "img_loc";

    const ITEM = "ITEM";
    const ITEM_DAMAGE = "item_id";
    const ITEM_ID = "item_id";
    const ITEM_META = self::ITEM_DAMAGE;

    const PARENTS = "parents";
    const PARENTS_ADD = "add_parent";
    const PARENTS_REMOVE = "remove_parent";

    const PRICE_BUY = "buy_price";
    const PRICE_SELL = "sell_price";

    const SETTINGS = "settings";

}