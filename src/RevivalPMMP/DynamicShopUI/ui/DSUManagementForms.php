<?php

namespace RevivalPMMP\DynamicShopUI\ui;


use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use RevivalPMMP\DynamicShopUI\data\DataKeys;
use RevivalPMMP\DynamicShopUI\data\DSUConfig;
use RevivalPMMP\DynamicShopUI\DynamicShopUI;
use RevivalPMMP\DynamicShopUI\elements\DSUCategory;
use RevivalPMMP\DynamicShopUI\elements\DSUItem;

class DSUManagementForms extends DSUForms{

	public function dsuManageMainForm(Player $player){
		$form = $this->formAPI->createSimpleForm([$this, "handleManageMainForm"]);
		$form->setTitle($this->shopName . " - Manage");
		$form->addButton("Update Items");
		$form->addButton("Update Categories");
		if($player->hasPermission("dsu.configuration")){
			$form->addButton("Update DynamicShopUI Settings");
		}
		$form->sendToPlayer($player);
	}
	public function handleManageMainForm(Player $player, array $data){
		$result = $data[0];
		if ($result === null) {
			// Player clicked the close button
			return;
		}

		switch($result){
			case 0:
				$this->updateItemsForm($player);
				break;
			case 1:
				$this->updateCategoriesForm($player);
				break;
		}
	}

	public function  updateItemsForm(Player $player, string $msg = "") {
		$form = $this->plugin->getFormAPI()->createSimpleForm([$this,"handleUpdateItemsForm"]);
		$form->setTitle("§f§5Items §f- Choose Action");
		$form->setContent($msg);
		$form->addButton("Add Item");
		$form->addButton("Modify Item");
		$form->addButton("Delete Item");
		$form->addButton("Back");
		$form->sendToPlayer($player);
	}

	public function handleUpdateItemsForm(Player $player, array $data){
		$result = $data[0];
		if($result === null){
			return;
		}
		switch($result){
			case 0:
				$this->addItemForm($player);
				break;
			case 1:
				$this->selectItemToModify($player);
				break;
			case 2:
				$this->selectItemToDelete($player);
				break;
			case 3:
				$this->dsuManageMainForm($player);
				break;
		}
	}

	public function addItemForm(Player $player){
		$categories = ["None"];

		/**
		 * @var DSUCategory $category;
		 */
		foreach($this->plugin->getSettings()->getAllCategories() as $category) {
			array_push($categories, $category->getName());
		}
		$this->options[$player->getUniqueId()->toString()] = $categories;
		$form = $this->formAPI->createCustomForm([$this, "handleAddItemForm"]);
		$form->setTitle("Item - Add");
		$form->addInput("Item ID", "236");
		$form->addInput("Meta", "10");
		$form->addDropdown("Parent", $categories);
		$form->addInput("Sell Price", "100");
		$form->addInput("Image URL", "http://yoursite.com/imagefile.png");
		$form->addToggle("Buy this item. (No / Yes)", false);
		$form->addInput("Buy Price (each)", "10");
		$form->sendToPlayer($player);
	}

	public function handleAddItemForm(Player $player, array $data) {
		$result = $data;
		if($result[0] === null){
			return;
		}
		if($result[0] === 0){
			$this->updateItemsForm($player);
		}
		$id = $result[0];
		$meta = $result[1];
		$parent = ($result[2] !== 0) ? $this->options[$player->getUniqueId()->toString()][$result[2]] : "";
		$sellPrice = floatval($result[3]);
		$imageURL = $result[4];
		$buyPrice = $result[5] ? $result[6] : 0;

		$item = ItemFactory::get($id, $meta);
		if($item instanceof Item){
			$name = $item->getName();
			$this->plugin->getSettings()->setItem($name, $id, $meta, $sellPrice, $buyPrice, $parent, $imageURL);
			$msg = "§2Successfully added item §f§l$name §r§2to the shop!";
		} else {
			$msg = "§f$id §cis not a valid item.  Please try again.";
		}
		$this->updateItemsForm($player, $msg);
	}

	public function selectItemToModify(Player $player){
		$form = $this->formAPI->createCustomForm([$this, "handleSelectItemToModify"]);
		$form->setTitle("§5§lSelect Item to Modify");
		$form->addLabel("Input Item Name or Item ID and Meta\n");
		$form->addInput("Item Name", "Purple Concrete");
		$form->addLabel("\nor");
		$form->addInput("\nItemID", "236");
		$form->addInput("Meta", "10");
		$form->sendToPlayer($player);
	}

	public function handleSelectItemToModify(Player $player, array $data) {
		$result = $data;
		if(!isset($result[1])){
			return;
		}
		if($result[1] !== ""){
			if($this->plugin->getSettings()->isShopItem($result[1])){
				$this->modifyItemForm($player, $this->plugin->getSettings()->getItem($result[1]));
				return;
			}
		}
		if($result[3] > 0){
			$item = ItemFactory::get($result[3], $result[4]);
			if($item instanceof Item){
				$itemName = $item->getName();
				if($this->plugin->getSettings()->isShopItem($itemName)){
					$this->modifyItemForm($player, $this->plugin->getSettings()->getItem($itemName));
					return;
				}
			}
		}
		$this->updateItemsForm($player, "§cNot a valid shop item.  Please try again.");
	}

	public function modifyItemForm(Player $player, DSUItem $item){
		$itemName = $item->getName();
		$sellPrice = $item->getSellPrice();
		$buyPrice = $item->getBuyPrice();
		$imageURL = $item->getImage();
		$parents = [""];
		foreach($item->getAllParents() as $parent => $data){
			array_push($parents, $parent);
		}
		$categories = [""];
		foreach($this->plugin->getSettings()->getAllCategories() as $category => $data){
			if(!in_array($category, $parents)){
				array_push($categories, $category);
			}
		}

		$this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_ITEMS_KEY] = $item->getName();
		$this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_PARENTS_KEY] = $parents;
		$this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $categories;

		$form = $this->formAPI->createCustomForm([$this, "handleModifyItemForm"]);
		$form->setTitle("§5§lModify Item - §f$itemName");
		$form->addInput("Sell Price", $sellPrice);
		$form->addInput("Buy Price", $buyPrice);
		$form->addInput("Image URL", $imageURL);
		$form->addDropdown("Remove Parent", $parents);
		$form->addDropdown("Add Parent", $categories);
		$form->sendToPlayer($player);
	}

	public function handleModifyItemForm(Player $player, array $data){
		$result = $data;
		$itemName = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_ITEMS_KEY];
		$item = $this->plugin->getSettings()->getItem($itemName);
		$parents = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_PARENTS_KEY];
		$categories = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY];
		$updated = false;

		if(!isset($result[1])){
			return;
		}
		if(($sellPrice = $result[0]) !== ""){
			$updated = true;
			$item->setSellPrice(floatval($sellPrice));
		}
		if(($buyPrice = $result[1] !== "")){
			$updated = true;
			$item->setBuyPrice(floatval($buyPrice));
		}
		if($result[2] !== ""){
			$updated = true;
			$item->setImage($result[2]);
		}
		if($result[3] !== 0){
			$updated = true;
			$parent = $parents[$result[3]];
			$item->removeParent($parent);
		}
		if($result[4] !== 0){
			$updated = true;
			$newParent = $this->plugin->getSettings()->getCategory($categories[$result[4]]);
			$item->addParent($newParent);
		}
		if($updated){
			$itemName = $item->getName();
			$msg = "§aUpdated §d$itemName";
			$this->plugin->getSettings()->saveShopData();
		} else {
			$msg = null;
		}
		$this->updateItemsForm($player, $msg);
	}

	public function selectItemToDelete(Player $player){
		$form = $this->formAPI->createCustomForm([$this, "handleSelectItemToDelete"]);
		$form->setTitle("§5§lSelect Item to Delete");
		$form->addLabel("Input Item Name or Item ID and Meta\n");
		$form->addInput("Item Name", "Purple Concrete");
		$form->addLabel("\nor");
		$form->addInput("\nItemID", "236");
		$form->addInput("Meta", "10");
		$form->sendToPlayer($player);
	}

	public function handleSelectItemToDelete(Player $player, array $data){
		// Consider Merging this with handleSelectItemToModify
		$result = $data;
		if(!isset($result[1])){
			return;
		}
		$deleted = false;
		if($result[1] !== ""){
			if($this->plugin->getSettings()->isShopItem($result[1])){
				$itemName = $result[1];
				$this->plugin->getSettings()->removeItem($result[1]);
				$deleted = true;
			}
		}
		if($result[3] > 0){
			$item = ItemFactory::get($result[3], $result[4]);
			if($item instanceof Item){
				$itemName = $item->getName();
				if($this->plugin->getSettings()->isShopItem($itemName)){
					$this->plugin->getSettings()->removeItem($itemName);
					$deleted = true;
				}
			}
		}

		if($deleted){
			$msg = "§d$itemName §adeleted successfully.";
		} else {
			$msg = "§cItem not deleted. Something went terrible wrong. \nResult 1 = $result[1]\nResult 3 = $result[3]\nResult 4 = $result[4] ";
		}
		$this->updateItemsForm($player, $msg);
	}

	public function updateCategoriesForm(Player $player) {
		$form = $this->plugin->getFormAPI()->createSimpleForm([$this,"handleUpdateCategoriesForm"]);
		$form->setTitle("Categories - Choose Action");
		$form->addButton("Add Category");
		$form->addButton("Modify Category");
		$form->addButton("Back");
		$form->sendToPlayer($player);
	}

	public function handleUpdateCategoriesForm(Player $player, array $data){
		$result = $data[0];
		if($result === null) {
			// Player closed the form.
			return;
		}
		switch($result){
			case 0:
				$this->addCategoryForm($player);
				break;

			case 1:
				$this->modifyCategoryForm($player);
				break;

			case 2:
				$this->dsuManageMainForm($player);
				break;
		}
	}

	public function addCategoryForm(Player $player) {
		$parentChoices = ["None"];
		/**
		 * @var DSUCategory $category;
		 */
		foreach($this->plugin->getSettings()->getAllCategories() as $category){
			array_push($parentChoices, $category->getName());
		}
		$this->options[$player->getUniqueId()->toString()] = $parentChoices;
		$form = $this->formAPI->createCustomForm([$this, "addCategory"]);
		$form->setTitle("Add New Category");
		$form->addInput("Category Name");
		$form->addDropdown("Parent", $parentChoices);
		$form->addInput("Image URL");
		$form->sendToPlayer($player);
	}

	public function addCategory(Player $player, array $data) {
		$result = $data;
		if(!isset($result[1])){
			return;
		}
		$parent = null;
		$imgURL = "";
		if($result[0] !== ""){
			if($result[1] !== null and $result[1] !== 0){
				$parent = $this->options[$player->getUniqueId()->toString()][$result[1]];
			}
			if(isset($result[2]) and $result[2] !== null){
				$imgURL = $result[2];
			}
			$this->plugin->getSettings()->setCategory($result[0], $imgURL, $parent);
		}
		$this->updateCategoriesForm($player);
	}

	public function modifyCategoryForm(Player $player, string $msg = null) {
		$form = $this->formAPI->createSimpleForm([$this,"handleModifyCategoryForm"]);
		$form->setTitle("Modify Category");
		if($msg !== null){
			$form->setContent(TextFormat::RED . TextFormat::BOLD . $msg);
		}
		$form->addButton("Add Parent");
		$form->addButton("Remove Parent");
		$form->addButton("Update Image");
		$form->addButton("Delete Category");
		$form->addButton("Back");
		$form->sendToPlayer($player);
	}

	public function handleModifyCategoryForm(Player $player, array $data) {
		$result = $data[0];
		if($result === null){
			// Player closed the form
			return;
		}
		switch($result){
			case 0:
				$this->addCategoryParentForm($player);
				break;
			case 1:
				$this->categorySelectionForm($player, true);
				break;
			case 2:
				$this->categorySelectionForm($player);
				break;
			case 3:
				$this->deleteCategoryForm($player);
				break;
			case 4:
				$this->updateCategoriesForm($player);
				break;
		}
	}

	public function addCategoryParentForm(Player $player) {
		$availableCategories = [];
		foreach($this->plugin->getSettings()->getAllCategories() as $category => $data){
			array_push($availableCategories, $category);
		}
		$this->options[$player->getUniqueId()->toString()] = $availableCategories;
		$form = $this->formAPI->createCustomForm([$this, "handleAddCategoryParentForm"]);
		$form->setTitle("Category - Add Parent");
		$form->addDropdown("Category to Update", $availableCategories);
		$form->addDropdown("Parent Category to Add", $availableCategories);
		$form->sendToPlayer($player);

	}

	public function handleAddCategoryParentForm(Player $player, array $data) {
		$result = $data;
		if(!isset($result[1])){
			return;
		}
		if($result[0] === $result[1]){
			$this->modifyCategoryForm($player, "Categories cannot have a parent of itself.");
		}else{
			$category = $this->options[$player->getUniqueId()->toString()][$result[0]];
			$image = $this->plugin->getSettings()->getCategory($category)->getImage();
			$parent = $this->options[$player->getUniqueId()->toString()][$result[1]];
			$this->plugin->getSettings()->setCategory($category, $image, $parent);
			$this->modifyCategoryForm($player);
		};
	}

	public function categorySelectionForm(Player $player, bool $remove = false){
		$form = $this->formAPI->createCustomForm([$this, "handleCategorySelectionForm"]);

		$categories = [];

		foreach($this->plugin->getSettings()->getAllCategories() as $category => $value){
			array_push($categories, $category);
		}

		$this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $categories;
		$this->options[$player->getUniqueId()->toString()]["remove"] = $remove;

		$form->setTitle("Category Selection");
		$form->addLabel("\n"); // Adding some blank lines for visual effect.
		$form->addDropdown("Choose Category", $categories);
		$form->sendToPlayer($player);
	}

	public function handleCategorySelectionForm(Player $player, array $data) {
		$result = $data;
		var_dump($result);
		if(!isset($result[1])){
			return;
		}
		$selectedCategory = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY][$result[1]];
		$remove = $this->options[$player->getUniqueId()->toString()]["remove"];
		if($remove){
			if(count($this->plugin->getSettings()->getCategory($selectedCategory)->getAllParents()) > 0){
				$this->removeCategoryParentForm($player, $selectedCategory);
				return;
			}else{
				$this->modifyCategoryForm($player, TextFormat::RED . "$selectedCategory does not have any parents.");
				return;
			}
		} else {
			$this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $selectedCategory;
			$this->updateImageCategoryForm($player, $selectedCategory);
			return;
		}
	}

	public function removeCategoryParentForm(Player $player, string $category){
		$parents = $this->plugin->getSettings()->getCategory($category)->getAllParents();
		if($parents !== null or count($parents) < 1){
			$form = $this->formAPI->createCustomForm([$this, "handleRemoveCategoryParentForm"]);
			$options = [];
			/**
			 * @var DSUCategory $parent;
			 */
			foreach($parents as $parent){
				array_push($options, $parent->getName());
			}
			$form->setTitle("Remove parent from " . TextFormat::DARK_PURPLE . $category);
			$form->addDropdown("\nSelect parent to remove.", $options);
			array_push($options, $category);
			$this->options[$player->getUniqueId()->toString()] = $options;
			$form->sendToPlayer($player);
		} else {
			$this->modifyCategoryForm($player, TextFormat::RED . "$category doesn't have any parents.");
		}
	}

	public function handleRemoveCategoryParentForm(Player $player, array $data) {
		$result = $data;
		if(!isset($result[0]) or $result[0] === null){
			return;
		}
		$targetCategoryName = $this->options[$player->getUniqueId()->toString()][count($this->options[$player->getUniqueId()->toString()]) - 1];
		$targetCategory = $this->plugin->getSettings()->getCategory($targetCategoryName);
		$selectedCategory = $this->options[$player->getUniqueId()->toString()][$result[0]];
		$targetCategory->removeParent($selectedCategory);
		$this->modifyCategoryForm($player, TextFormat::GREEN . "Successfully removed" . TextFormat::BLUE . $selectedCategory . TextFormat::GREEN . " from " . TextFormat::BLUE . $targetCategoryName);
	}

	public function updateImageCategoryForm(Player $player, string $category) {
		$imgURL = $this->plugin->getSettings()->getCategory($category)->getImage();
		if($imgURL === null or $imgURL == ""){
			$imgURL = "http://www.mysite.com/image.png";
		}
		$form = $this->plugin->getFormAPI()->createCustomForm([$this, "handleUpdateImageCategoryForm"]);
		$form->setTitle("");
		$form->addInput("New Image URL", $imgURL);
		$form->sendToPlayer($player);
	}

	public function handleUpdateImageCategoryForm(Player $player, array $data){
		$result = $data;
		if($result === null or $result[0] === null){
			return;
		}
		$categoryName = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY];
		$selectedCategory = $this->plugin->getSettings()->getCategory($categoryName);
		$selectedCategory->setImage($result[0]);
		$this->modifyCategoryForm($player, "Image Updated Successfully!");
		return;
	}

	public function deleteCategoryForm(Player $player){
		$form = $this->formAPI->createCustomForm([$this, "handleDeleteCategoryForm"]);
		$categories = [];
		/**
		 * @var DSUCategory $category;
		 */
		foreach($this->plugin->getSettings()->getAllCategories() as $category){
			array_push($categories, $category->getName());
		}
		$this->options[$player->getUniqueId()->toString()] = $categories;
		$form->setTitle(TextFormat::RED . "Delete Category");
		$form->addDropdown("\n\nSelect Category to Delete", $categories);
		$form->sendToPlayer($player);
	}

	public function handleDeleteCategoryForm(Player $player, array $data){
		$result = $data;
		if(!isset($result[0]) or $result[0] === null){
			return;
		}
		$selectedCategory = $this->options[$player->getUniqueId()->toString()][$result[0]];
		$form = $this->formAPI->createSimpleForm([$this, "handleConfirmDeleteCategoryForm"]);
		$form->setTitle(TextFormat::RED . "Confirm Delete of $selectedCategory");
		$form->setContent(TextFormat::BOLD . TextFormat::RED . "Are you sure you want to delete " . TextFormat::BLUE . $selectedCategory . TextFormat::RED . "?");
		$form->addButton(TextFormat::BOLD . TextFormat::RED . "NO");
		$form->addButton(TextFormat::BOLD . TextFormat::GREEN . "Yes");
		$this->options[$player->getUniqueId()->toString()][0] = $selectedCategory;
		$form->sendToPlayer($player);
	}

	public function handleConfirmDeleteCategoryForm(Player $player, array $data){
		$result = $data;
		if(!isset($result[0]) or $result[0] === null){
			return;
		}
		if($result[0] === 1){
			$selectedCategory = $this->options[$player->getUniqueId()->toString()][0];
			$this->plugin->getSettings()->removeCategory($selectedCategory);
			$msg = TextFormat::GREEN . "Deleted " . TextFormat::DARK_PURPLE . $selectedCategory;
		} else {
			$msg = TextFormat::RED . "Delete Category Aborted.";
		}
		$this->modifyCategoryForm($player, $msg);


	}
}