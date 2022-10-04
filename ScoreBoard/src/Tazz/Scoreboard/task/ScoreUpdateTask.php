<?php
declare(strict_types = 1);

#Author: Tazz

namespace Tazz\ScoreBoard\task;

use Tazz\ScoreBoard\Main;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class ScoreUpdateTask extends Task{
	
	/** @var Main */
	private $plugin;
	/** @var int */
	private $titleIndex = 0;
	
	/**
	 * ScoreUpdateTask constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->titleIndex = 0;
	}
	
	/**
	 * @param int $tick
	 */
	public function onRun(): void{
		$players = $this->plugin->getServer()->getOnlinePlayers();
		$dataConfig = new Config($this->plugin->getDataFolder() . "data.yml", Config::YAML);
		$titles = $dataConfig->get("server-names");
		if((is_null($titles)) || empty($titles) || !isset($titles)){
			$this->plugin->getLogger()->error("Please set server-names in data.yml properly.");
			$this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
			return;
		}
		if(!isset($titles[$this->titleIndex])){
			$this->titleIndex = 0;
		}
		foreach($players as $player){
			$this->plugin->addScore($player, $titles[$this->titleIndex]);
		}
		$this->titleIndex++;
	}
}