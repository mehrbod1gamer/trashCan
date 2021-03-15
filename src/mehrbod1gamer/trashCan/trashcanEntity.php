<?php

namespace mehrbod1gamer\trashCan;

use muqsit\invmenu\InvMenu;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class trashcanEntity extends Human
{
    public $menu;

    public function __construct(Level $level, CompoundTag $nbt)
    {
        $this->menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $this->menu->setName("TrashCan")->setListener([$this, "listener"]);
        parent::__construct($level, $nbt);
    }

    public function saveNBT(): void
    {
        parent::saveNBT();
    }

    public function attack(EntityDamageEvent $source): void
    {
        if (!$source instanceof EntityDamageByEntityEvent) return;
        $damager = $source->getDamager();
        if (!$damager instanceof Player ) return;

        if(!isset(main::$removers[$damager->getName()])) {
            $this->getMenu()->send($damager);
        } else {
            unset(main::$removers[$damager->getName()]);
            $damager->sendMessage(TextFormat::GREEN . "TrashCan Removed");
            $this->close();
        }
        return;
    }

    public function getMenu() : InvMenu
    {
        return $this->menu;
    }

    public function listener(Player $player, Item $item):bool
    {
        return true;
    }
}
