<?php
namespace RevivalPMMP\DynamicShopUI\data;

Interface DataKeys{

	const PLAYER_DATA_OPTION_ITEM = "isItem"; //bool
	const PLAYER_DATA_OPTIONS_KEY = "options"; //array
	const PLAYER_DATA_PREVIOUS_KEY = "previous"; //string name of previous category

	//Categories
	const SHOP_DATA_CATEGORIES_KEY = "Categories";

	//Items
	const SHOP_DATA_ITEMS_KEY = "Items";
	const SHOP_DATA_ITEM_ID_KEY = "ID";
	const SHOP_DATA_ITEM_META_KEY = "Meta";
	const SHOP_DATA_ITEM_BUY_PRICE_KEY = "BuyPrice";
	const SHOP_DATA_ITEM_SELL_PRICE_KEY = "SellPrice";

	//Others
	const SHOP_DATA_PARENTS_KEY = "parents";
	const SHOP_DATA_IMAGE_KEY = "image";

}