<?php

namespace PlayerBounty\Commands;

use PlayerBounty\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;

class PlayerBountyCommand extends Command {

    private Loader $loader;

    public function __construct(Loader $loader) {
        parent::__construct("playerbounty", "Set a kill bounty to a player and earn money!");
        $this->loader = $loader;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($sender instanceof Player) {
            if(isset($args[0])) {
                switch($args[0]) {
                    case "set":
                        if(isset($args[1])) {
                            if($this->loader->getServer()->getPlayerByPrefix($args[1])->getName() == $sender->getName()) {
                                $sender->sendMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("same-name-error"), [
                                    "PREFIX" => $this->loader->config->get("prefix")
                                ])));
                            } else {
                                if((int)$args[2] > $this->loader->getMoney($sender)) {
                                    $sender->sendMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("not-enough-money"), [
                                        "PREFIX" => $this->loader->config->get("prefix")
                                    ])));
                                } else {
                                    if($this->loader->getServer()->getPlayerByPrefix($args[1])->isOnline()) {
                                        if (!$this->loader->isBountyList($this->loader->getServer()->getPlayerByPrefix($args[1])->getName())) {
                                            $this->loader->addBountyList($this->loader->getServer()->getPlayerByPrefix($args[1])->getName(), (int)$args[2]);
                                            $this->loader->getServer()->broadcastMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("broadcast-bounty"), [
                                                "PREFIX" => $this->loader->config->get("prefix"),
                                                "BETTOR" => $sender->getName(),
                                                "PLAYER" => $this->loader->getServer()->getPlayerByPrefix($args[1])->getName(),
                                                "MONEY" => $args[2]
                                            ])));
                                            $this->loader->removeMoney($sender, $args[2]);
                                        } else {
                                            $sender->sendMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("player-already-bounty"), [
                                                "PREFIX" => $this->loader->config->get("prefix")
                                            ])));
                                        }
                                    } else {
                                        $sender->sendMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("player-not-online"), [
                                            "PREFIX" => $this->loader->config->get("prefix")
                                        ])));
                                    }
                                }
                            }} else {
                            $sender->sendMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("usage-message"), [
                                "PREFIX" => $this->loader->config->get("prefix")
                            ])));
                        }
                        break;
                    case "list":
                        if(count($this->loader->bounty) > 0) {
                            $this->bountyList($sender);
                        } else {
                            $sender->sendMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("not-enough-players-bounty"), [
                                "PREFIX" => $this->loader->config->get("prefix")
                            ])));
                        }
                        break;
                }

            } else {
                $sender->sendMessage(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("usage-message"), [
                    "PREFIX" => $this->loader->config->get("prefix")
                ])));
            }
        }
    }

    public function bountyList(Player $player) {

        $form = new SimpleForm(function (Player $player, int $data = null){
            return true;
        });
        $form->setTitle(TextFormat::colorize($this->loader->getConfigMessage("prefix")));
        $bountylist = $this->loader->bounty;
        foreach($bountylist as $key => $index) {
            $form->addButton(TextFormat::colorize($this->loader->replaceVars($this->loader->config->get("bountyUI-list"), [
                "PLAYER" => strval($key),
                "MONEY" => strval($index)
            ])));
        }
        $player->sendForm($form);
    }

}