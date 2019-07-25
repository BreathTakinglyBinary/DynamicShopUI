<?php

namespace BreathTakinglyBinary\DynamicShopUI\data;

Interface DataKeys{

    const SHOP_DATA_FILE_NAME = "shopData.yml";

    const PLAYER_DATA_OPTION_ITEM = "isItem"; //bool
    const PLAYER_DATA_OPTIONS_KEY = "options"; //array
    const PLAYER_DATA_PREVIOUS_KEY = "previous"; //string name of previous category

    //Categories
    const SHOP_DATA_CATEGORIES_KEY = "Categories";

    //Items
    const SHOP_DATA_ITEMS_KEY = "Items";
    const SHOP_DATA_ITEM_ID_KEY = "ID";
    const SHOP_DATA_ITEM_META_KEY = "Meta";
    const SHOP_DATA_ITEM_CAN_BUY = "CanBuy";
    const SHOP_DATA_ITEM_BUY_PRICE_KEY = "BuyPrice";
    const SHOP_DATA_ITEM_CAN_SELL = "CanSell";
    const SHOP_DATA_ITEM_SELL_PRICE_KEY = "SellPrice";

    //Others
    const SHOP_DATA_PARENTS_KEY = "parents";
    const SHOP_DATA_IMAGE_KEY = "image";

}