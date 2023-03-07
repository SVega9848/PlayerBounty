<?php

namespace PlayerBounty\Events;

use PlayerBounty\Loader;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Events implements Listener {

    private Loader $loader;

    public function __construct(Loader $loader) {
        $this->loader = $loader;
    }

    public function onDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
        $cause = $event->getPlayer()->getLastDamageCause();
        if($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if($damager instanceof Player) {
                if($this->loader->isBountyList($player->getName())) {
                    $this->loader->getServer()->broadcastMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("broadcast-bounty-kill"), [
                        "PREFIX" => $this->loader->config->get("prefix"),
                        "KILLER" => $damager->getName(),
                        "KILLED" => $player->getName(),
                        "MONEY" => strval($this->loader->bounty[$player->getName()])
                    ])));
                    $this->loader->addMoney($damager, $this->loader->bounty[$player->getName()]);
                    $this->loader->deleteBountylist($player->getName());
                } else {
                    $this->loader->getServer()->broadcastMessage("PRUEBAAA");
                }
            }
        }
    }

}