<?php

namespace mehrbod1gamer\trashCan;

use mehrbod1gamer\trashCan\Lib\invmenu\InvMenuHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class main extends PluginBase implements Listener
{
    public static $removers = [];

    public function onEnable()
    {
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        Entity::registerEntity(trashcanEntity::class, true);
        parent::onEnable();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "trashcan":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(TextFormat::DARK_RED . "[ ! ] use this command in game");
                    return false;
                }

                if (!isset($args[0])) {
                    $sender->sendMessage(TextFormat::YELLOW . "Usage >> /trashcan <get/rem>");
                    return false;
                }

                if ($args[0] == 'get') {
                    if ($sender->isOp()) {
                        $item = Item::get(Item::SKULL_BLOCK);
                        $item->setCustomName(TextFormat::DARK_GRAY . "TrashCan\n" . TextFormat::YELLOW . ">Put on ground<");
                        $item->setLore([
                            "trashcan"
                        ]);
                        $sender->getInventory()->addItem($item);
                        $sender->sendMessage(TextFormat::DARK_GREEN . "[ + ] TrashCan item added to inventory");
                    } else {
                        $sender->sendMessage(TextFormat::DARK_GREEN . "[ ! ] You are not OP");
                        return false;
                    }
                } elseif ($args[0] == 'rem') {
                    self::$removers[$sender->getName()] = true;
                    $sender->sendMessage(TextFormat::YELLOW . ">> click to TrashCan to remove");
                } else {
                    $sender->sendMessage(TextFormat::YELLOW . "Usage >> /trashcan <get/rem>");
                }
        }
        return parent::onCommand($sender, $command, $label, $args);
    }

    public function onBlockPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        $heldItem = $player->getInventory()->getItemInHand();
        $lore =  $heldItem->getLore();
        if (!isset($lore[0])) return;

        if ($lore[0] == 'trashcan' ) {
            $event->setCancelled(true);
            $nbt = Entity::createBaseNBT($event->getBlock()->asVector3()->add(0.5,0,0.5));

            $skinPath = $this->getFile()."resources/trashcan.png";
            $img = @imagecreatefrompng($skinPath);
            $skinbytes = "";
            $s = (int)@getimagesize($skinPath)[1];
            for($y = 0; $y < $s; $y++) {
                for($x = 0; $x < 64; $x++) {
                    $colorat = @imagecolorat($img, $x, $y);
                    $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                    $r = ($colorat >> 16) & 0xff;
                    $g = ($colorat >> 8) & 0xff;
                    $b = $colorat & 0xff;
                    $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
                }
            }
            @imagedestroy($img);

             $nbt->setTag(new CompoundTag('Skin', [
                "Name" => new StringTag("Name", $player->getSkin()->getSkinId()),
                "Data" => new ByteArrayTag("Data", $skinbytes),
                "GeometryName" => new StringTag("GeometryName", "geometry.trashcan"),
                "GeometryData" => new ByteArrayTag("GeometryData", file_get_contents($this->getFile()."resources/trashcan.json"))
            ]));
            $trashcan = new trashcanEntity($player->getLevel(), $nbt);
            $trashcan->spawnToAll();
        }
    }
}
