<?php

namespace BreathTakinglyBinary\DynamicShopUI\ui;


use BreathTakinglyBinary\DynamicShopUI\data\DataKeys;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUCategory;
use BreathTakinglyBinary\DynamicShopUI\elements\DSUItem;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DSUManagementForms extends DSUForms{

    public function dsuManageMainForm(Player $player){
        $form = new SimpleForm([$this, "handleManageMainForm"]);
        $form->setTitle($this->shopName . " - Manage");
        $form->addButton("Update Items");
        $form->addButton("Update Categories");
        if($player->hasPermission("dsu.configuration")){
            $form->addButton("Update DynamicShopUI Settings");
        }
        $player->sendForm($form);
    }

    public function handleManageMainForm(Player $player, $data){
        if($data === null){
            // Player clicked the close button
            return;
        }

        switch($data){
            case 0:
                $this->updateItemsForm($player);
                break;
            case 1:
                $this->updateCategoriesForm($player);
                break;
            case 2:
                $player->sendMessage("Update Settings Feature is not available yet.");
        }
    }

    public function updateItemsForm(Player $player, string $msg = ""){
        $form = new SimpleForm([$this, "handleUpdateItemsForm"]);
        $form->setTitle("§f§5Items §f- Choose Action");
        $form->setContent($msg);
        $form->addButton("Add Item");
        $form->addButton("Modify Item");
        $form->addButton("Delete Item");
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function handleUpdateItemsForm(Player $player, $data){
        if($data === null){
            return;
        }
        switch($data){
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
         * @var DSUCategory $category ;
         */
        foreach($this->manager->getCategories() as $category){
            array_push($categories, $category->getName());
        }
        $this->options[$player->getUniqueId()->toString()] = $categories;
        $form = new CustomForm([$this, "handleAddItemForm"]);
        $form->setTitle("Item - Add");
        $form->addInput("Item ID", "236");
        $form->addInput("Meta", "10");
        $form->addDropdown("Parent", $categories);
        $form->addInput("Sell Price", "100");
        $form->addInput("Image URL", "http://yoursite.com/imagefile.png");
        $form->addToggle("Buy this from players. (No / Yes)", false);
        $form->addInput("Buy Price (each)", "10");
        $player->sendForm($form);
    }

    public function handleAddItemForm(Player $player, ?array $data){
        $result = $data;
        if($result[0] === null){
            return;
        }
        if($result[0] === 0){
            $this->updateItemsForm($player);
        }
        $id = $result[0];
        $meta = $result[1];
        $item = ItemFactory::get($id, $meta);
        if(!$item instanceof Item){
            $msg = "§f$id §cis not a valid item.  Please try again.";
            $this->updateItemsForm($player, $msg);
            return;
        }
        $parent = ($result[2] !== 0) ? $this->options[$player->getUniqueId()->toString()][$result[2]] : "";
        $sellPrice = floatval($result[3]);
        $imageURL = $result[4];
        $buyPrice = $result[5] ? $result[6] : 0;

        $name = $item->getName();
        $newItem = new DSUItem($name, $id, $meta, $buyPrice, $sellPrice, $imageURL, ($sellPrice > 0), $result[5]);
        if($parent !== null and $parent !== ""){
            $newItem->addParent($parent);
        }
        $this->manager->addItem($newItem);
        $msg = "§2Successfully added item §f§l$name §r§2to the shop!";

        $this->updateItemsForm($player, $msg);
    }

    public function selectItemToModify(Player $player){
        $form = new CustomForm([$this, "handleSelectItemToModify"]);
        $form->setTitle("§5§lSelect Item to Modify");
        $form->addLabel("Input Item Name or Item ID and Meta\n");
        $form->addInput("Item Name", "Purple Concrete");
        $form->addLabel("\nor");
        $form->addInput("\nItemID", "236");
        $form->addInput("Meta", "10");
        $player->sendForm($form);
    }

    public function handleSelectItemToModify(Player $player, ?array $data){
        $result = $data;
        if(!isset($result[1])){
            return;
        }
        $item = null;
        if($result[1] !== ""){
            $item = $this->manager->getItemByName($result[1]);
        }elseif($result[3] > 0){
            $item = $this->manager->getItemById($result[3], $result[4]);
        }
        if($item instanceof DSUItem){
            $this->modifyItemForm($player, $item);

            return;
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
        foreach($this->manager->getCategories() as $category => $data){
            if(!in_array($category, $parents)){
                array_push($categories, $category);
            }
        }

        $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_ITEMS_KEY] = $item->getName();
        $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_PARENTS_KEY] = $parents;
        $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $categories;

        $form = new CustomForm([$this, "handleModifyItemForm"]);
        $form->setTitle("§5§lModify Item - §f$itemName");
        $form->addInput("Sell Price", $sellPrice);
        $form->addInput("Buy Price", $buyPrice);
        $form->addInput("Image URL", $imageURL);
        $form->addDropdown("Remove Parent", $parents);
        $form->addDropdown("Add Parent", $categories);
        $player->sendForm($form);
    }

    public function handleModifyItemForm(Player $player, ?array $data){
        $result = $data;
        $itemName = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_ITEMS_KEY];
        $item = $this->manager->getItemByName($itemName);
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
            if($this->manager->getCategoryByName($categories[$result[4]]) instanceof DSUCategory){
                $item->addParent($categories[$result[4]]);
            }
        }
        if($updated){
            $itemName = $item->getName();
            $msg = "§aUpdated §d$itemName";
            $this->manager->saveShopData();
        }else{
            $msg = null;
        }
        $this->updateItemsForm($player, $msg);
    }

    public function selectItemToDelete(Player $player){
        $form = new CustomForm([$this, "handleSelectItemToDelete"]);
        $form->setTitle("§5§lSelect Item to Delete");
        $form->addLabel("Input Item Name or Item ID and Meta\n");
        $form->addInput("Item Name", "Purple Concrete");
        $form->addLabel("\nor");
        $form->addInput("\nItemID", "236");
        $form->addInput("Meta", "10");
        $player->sendForm($form);
    }

    public function handleSelectItemToDelete(Player $player, ?array $data){
        // Consider Merging this with handleSelectItemToModify
        $result = $data;
        if(!isset($result[1])){
            return;
        }
        $itemName = "";
        $deleted = false;
        if($result[1] !== ""){
            if(($shopItem = $this->manager->getItemByName($result[1])) instanceof DSUItem){
                $itemName = $result[1];
                $this->manager->removeItem($shopItem);
                $deleted = true;
            }
        }
        if(!$deleted and $result[3] > 0){
            $item = $this->manager->getItemById($result[3], $result[4]);
            if($item instanceof DSUItem){
                $itemName = $item->getName();
                $this->manager->removeItem($item);
                $deleted = true;
            }
        }
        if($deleted){
            $msg = "§d$itemName §adeleted successfully.";
        }else{
            $msg = "§cItem not deleted. Something went terrible wrong. \nResult 1 = $result[1]\nResult 3 = $result[3]\nResult 4 = $result[4] ";
        }
        $this->updateItemsForm($player, $msg);
    }

    public function updateCategoriesForm(Player $player){
        $form = new SimpleForm([$this, "handleUpdateCategoriesForm"]);
        $form->setTitle("Categories - Choose Action");
        $form->addButton("Add Category");
        $form->addButton("Modify Category");
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function handleUpdateCategoriesForm(Player $player, $data){
        if($data === null){
            // Player closed the form.
            return;
        }
        switch($data){
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

    public function addCategoryForm(Player $player){
        $parentChoices = ["None"];
        /**
         * @var DSUCategory $category ;
         */
        foreach($this->manager->getCategories() as $category){
            array_push($parentChoices, $category->getName());
        }
        $this->options[$player->getUniqueId()->toString()] = $parentChoices;
        $form = new CustomForm([$this, "addCategory"]);
        $form->setTitle("Add New Category");
        $form->addInput("Category Name");
        $form->addDropdown("Parent", $parentChoices);
        $form->addInput("Image URL");
        $player->sendForm($form);
    }

    public function addCategory(Player $player, ?array $data){
        $result = $data;
        if(!isset($result[1])){
            return;
        }
        $parentName = null;
        $imgURL = "";
        if($result[0] !== ""){
            if($result[1] !== null and $result[1] !== 0){
                $parentName = $this->options[$player->getUniqueId()->toString()][$result[1]];
            }
            if(isset($result[2]) and $result[2] !== null){
                $imgURL = $result[2];
            }
            $category = new DSUCategory($result[0], $imgURL);
            if($this->manager->getCategoryByName($parentName) instanceof DSUCategory){
                $category->addParent($parentName);
            }
            $this->manager->addCategory($category);
        }
        $this->updateCategoriesForm($player);
    }

    public function modifyCategoryForm(Player $player, string $msg = null){
        $form = new SimpleForm([$this, "handleModifyCategoryForm"]);
        $form->setTitle("Modify Category");
        if($msg !== null){
            $form->setContent(TextFormat::RED . TextFormat::BOLD . $msg);
        }
        $form->addButton("Add Parent");
        $form->addButton("Remove Parent");
        $form->addButton("Update Image");
        $form->addButton("Delete Category");
        $form->addButton("Back");
        $player->sendForm($form);
    }

    public function handleModifyCategoryForm(Player $player, $data){
        if($data === null){
            // Player closed the form
            return;
        }
        switch($data){
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

    public function addCategoryParentForm(Player $player){
        $availableCategories = [];
        foreach($this->manager->getCategories() as $category => $data){
            array_push($availableCategories, $category);
        }
        $this->options[$player->getUniqueId()->toString()] = $availableCategories;
        $form = new CustomForm([$this, "handleAddCategoryParentForm"]);
        $form->setTitle("Category - Add Parent");
        $form->addDropdown("Category to Update", $availableCategories);
        $form->addDropdown("Parent Category to Add", $availableCategories);
        $player->sendForm($form);

    }

    public function handleAddCategoryParentForm(Player $player, ?array $data){
        $result = $data;
        if(!isset($result[1])){
            return;
        }
        if($result[0] === $result[1]){
            $this->modifyCategoryForm($player, "Categories cannot have a parent of itself.");
        }else{
            $categoryName = $this->options[$player->getUniqueId()->toString()][$result[0]];
            $image = $this->manager->getCategoryByName($categoryName)->getImage();
            $parentName = $this->options[$player->getUniqueId()->toString()][$result[1]];
            $category = new DSUCategory($categoryName, $image);
            if($this->manager->getCategoryByName($parentName) instanceof DSUCategory){
                $category->addParent($parentName);
            }
            $this->manager->addCategory($category);
            $this->modifyCategoryForm($player);
        };
    }

    public function categorySelectionForm(Player $player, bool $remove = false){
        $form = new CustomForm([$this, "handleCategorySelectionForm"]);

        $categories = [];

        foreach($this->manager->getCategories() as $category => $value){
            array_push($categories, $category);
        }

        $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $categories;
        $this->options[$player->getUniqueId()->toString()]["remove"] = $remove;

        $form->setTitle("Category Selection");
        $form->addLabel("\n"); // Adding some blank lines for visual effect.
        $form->addDropdown("Choose Category", $categories);
        $player->sendForm($form);
    }

    public function handleCategorySelectionForm(Player $player, ?array $data){
        $result = $data;
        if(!isset($result[1])){
            return;
        }
        $selectedCategory = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY][$result[1]];
        $remove = $this->options[$player->getUniqueId()->toString()]["remove"];
        $category = $this->manager->getCategoryByName($selectedCategory);
        if(!$category instanceof DSUCategory){
            $this->plugin->getLogger()->error("Selected Category, $selectedCategory, did not return a DSUCategory object.");
            $this->modifyCategoryForm($player, "Something went wrong.");
        }
        if($remove){
            if(count($category->getAllParents()) > 0){
                $this->removeCategoryParentForm($player, $selectedCategory);

                return;
            }else{
                $this->modifyCategoryForm($player, TextFormat::RED . "$selectedCategory does not have any parents.");

                return;
            }
        }else{
            $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY] = $selectedCategory;
            $this->updateImageCategoryForm($player, $selectedCategory);

            return;
        }
    }

    public function removeCategoryParentForm(Player $player, string $categoryName){
        $category = $this->manager->getCategoryByName($categoryName);
        if(!$category instanceof DSUCategory){
            $this->plugin->getLogger()->error("Selected Category, $categoryName, did not return a DSUCategory object.");
            $this->modifyCategoryForm($player, "Something went wrong.");
        }
        $parents = $category->getAllParents();
        if($parents !== null or count($parents) < 1){
            $form = new CustomForm([$this, "handleRemoveCategoryParentForm"]);
            $options = [];
            /**
             * @var DSUCategory $parent ;
             */
            foreach($parents as $parent){
                array_push($options, $parent->getName());
            }
            $form->setTitle("Remove parent from " . TextFormat::DARK_PURPLE . $categoryName);
            $form->addDropdown("\nSelect parent to remove.", $options);
            array_push($options, $categoryName);
            $this->options[$player->getUniqueId()->toString()] = $options;
            $player->sendForm($form);
        }else{
            $this->modifyCategoryForm($player, TextFormat::RED . "$categoryName doesn't have any parents.");
        }
    }

    public function handleRemoveCategoryParentForm(Player $player, ?array $data){
        $result = $data;
        if(!isset($result[0]) or $result[0] === null){
            return;
        }
        $targetCategoryName = $this->options[$player->getUniqueId()->toString()][count($this->options[$player->getUniqueId()->toString()]) - 1];
        $targetCategory = $this->manager->getCategoryByName($targetCategoryName);
        $selectedCategory = $this->options[$player->getUniqueId()->toString()][$result[0]];
        $targetCategory->removeParent($selectedCategory);
        $this->modifyCategoryForm($player, TextFormat::GREEN . "Successfully removed" . TextFormat::BLUE . $selectedCategory . TextFormat::GREEN . " from " . TextFormat::BLUE . $targetCategoryName);
    }

    public function updateImageCategoryForm(Player $player, string $categoryName){
        $category = $this->manager->getCategoryByName($categoryName);
        if(!$category instanceof DSUCategory){

        }
        $imgURL = $category->getImage();
        if($imgURL === null or $imgURL == ""){
            $imgURL = "http://www.mysite.com/image.png";
        }
        $form = new CustomForm([$this, "handleUpdateImageCategoryForm"]);
        $form->setTitle("");
        $form->addInput("New Image URL", $imgURL);
        $player->sendForm($form);
    }

    public function handleUpdateImageCategoryForm(Player $player, ?array $data){
        $result = $data;
        if($result === null or $result[0] === null){
            return;
        }
        $categoryName = $this->options[$player->getUniqueId()->toString()][DataKeys::SHOP_DATA_CATEGORIES_KEY];
        $selectedCategory = $this->manager->getCategoryByName($categoryName);
        $selectedCategory->setImage($result[0]);
        $this->modifyCategoryForm($player, "Image Updated Successfully!");

        return;
    }

    public function deleteCategoryForm(Player $player){
        $form = new CustomForm([$this, "handleDeleteCategoryForm"]);
        $categories = [];
        /**
         * @var DSUCategory $category ;
         */
        foreach($this->manager->getCategories() as $category){
            array_push($categories, $category->getName());
        }
        $this->options[$player->getUniqueId()->toString()] = $categories;
        $form->setTitle(TextFormat::RED . "Delete Category");
        $form->addDropdown("\n\nSelect Category to Delete", $categories);
        $player->sendForm($form);
    }

    public function handleDeleteCategoryForm(Player $player, ?array $data){
        $result = $data;
        if(!isset($result[0]) or $result[0] === null){
            return;
        }
        $selectedCategory = $this->options[$player->getUniqueId()->toString()][$result[0]];
        $form = new SimpleForm([$this, "handleConfirmDeleteCategoryForm"]);
        $form->setTitle(TextFormat::RED . "Confirm Delete of $selectedCategory");
        $form->setContent(TextFormat::BOLD . TextFormat::RED . "Are you sure you want to delete " . TextFormat::BLUE . $selectedCategory . TextFormat::RED . "?");
        $form->addButton(TextFormat::BOLD . TextFormat::RED . "NO");
        $form->addButton(TextFormat::BOLD . TextFormat::GREEN . "Yes");
        $this->options[$player->getUniqueId()->toString()][0] = $selectedCategory;
        $player->sendForm($form);
    }

    public function handleConfirmDeleteCategoryForm(Player $player, $data){
        if($data === null){
            return;
        }
        if($data === 1){
            $selectedCategory = $this->options[$player->getUniqueId()->toString()][0];
            $this->manager->removeCategoryByName($selectedCategory);
            $msg = TextFormat::GREEN . "Deleted " . TextFormat::DARK_PURPLE . $selectedCategory;
        }else{
            $msg = TextFormat::RED . "Delete Category Aborted.";
        }
        $this->modifyCategoryForm($player, $msg);


    }
}