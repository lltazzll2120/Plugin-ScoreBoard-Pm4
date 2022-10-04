<?php
declare(strict_types = 1);

#Author: Tazz

namespace Tazz\ScoreBoard;

use Tazz\ScoreBoard\libs\Tazz\ScoreFactory\ScoreFactory;
use Tazz\ScoreBoard\commands\ScoreHudCommand;
use Tazz\ScoreBoard\data\DataManager;
use Tazz\ScoreBoard\task\ScoreUpdateTask;
use Tazz\ScoreBoard\libs\Tazz\UpdateNotifier\UpdateNotifier;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase{

	/** @var string */
	public const PREFIX = "§8[§6S§eH§8]§r ";
	/** @var string */

	/** @var array */
	public $disabledScoreHudPlayers = [];
	/** @var DataManager */
	private $dataManager;
	/** @var null|array */
	private $scoreboards = [];
	/** @var null|array */
	private $scorelines = [];

	public function onLoad(): void{
		$this->initScoreboards();

	}

		

	private function initScoreboards(): void{
		$this->saveDefaultConfig();
		$this->saveResource("data.yml");

		$dataConfig = new Config($this->getDataFolder() . "data.yml", Config::YAML);
		foreach($dataConfig->getNested("scoreboards") as $world => $data){
			$world = strtolower($world);
			$this->scoreboards[$world] = $data;
			$this->scorelines[$world] = $data["lines"];
		}
	}
	public function onEnable(): void{
		$this->dataManager = new DataManager($this);
		$this->setTimezone($this->getConfig()->get("timezone"));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new ScoreUpdateTask($this), (int) $this->getConfig()->get("update-interval") * 20);
		$this->getLogger()->info("ScoreHud Plugin Enabled.");
	}

	/**
	 * @param $timezone
	 * @return mixed
	 */
	private function setTimezone($timezone){
		if($timezone !== false){
			$this->getLogger()->notice("Server timezone successfully set to " . $timezone);

			return @date_default_timezone_set($timezone);
		}

		return false;
	}

	/**
	 * @param Player $player
	 * @param string $title
	 */
	public function addScore(Player $player, string $title): void{
		if(!$player->isOnline()){
			return;
		}
		if(isset($this->disabledScoreHudPlayers[strtolower($player->getName())])){
			return;
		}
		ScoreFactory::setScore($player, $title);
		$this->updateScore($player);
	}

	/**
	 * @param Player $player
	 */
	public function updateScore(Player $player): void{
		if($this->getConfig()->get("per-world-scoreboards")){
			if(!$player->isOnline()){
				return;
			}
			$levelName = strtolower($player->getLevel()->getFolderName());
			if(!is_null($lines = $this->getScorelines($levelName))){
				if(empty($lines)){
					$this->getLogger()->error("Please set lines key for $levelName correctly for scoreboards in data.yml.");
					$this->getServer()->getPluginManager()->disablePlugin($this);

					return;
				}
				$i = 0;
				foreach($lines as $line){
					$i++;
					if($i <= 15){
						ScoreFactory::setScoreLine($player, $i, $this->process($player, $line));
					}
				}
			}elseif($this->getConfig()->get("use-default-score-lines")){
				$this->displayDefaultScoreboard($player);
			}else{
				ScoreFactory::removeScore($player);
			}
		}else{
			$this->displayDefaultScoreboard($player);
		}
	}

	public function displayDefaultScoreboard(Player $player): void{
		$dataConfig = new Config($this->getDataFolder() . "data.yml", Config::YAML);

		$lines = $dataConfig->get("score-lines");
		if(empty($lines)){
			$this->getLogger()->error("Please set score-lines in data.yml properly.");
			$this->getServer()->getPluginManager()->disablePlugin($this);

			return;
		}
		$i = 0;
		foreach($lines as $line){
			$i++;
			if($i <= 15){
				ScoreFactory::setScoreLine($player, $i, $this->process($player, $line));
			}
		}
	}

	public function getScorelines(string $world): ?array{
		return !isset($this->scorelines[$world]) ? null : $this->scorelines[$world];
	}

	/**
	 * @param Player $player
	 * @param string $string
	 * @return string
	 */
	public function process(Player $player, string $string): string{
		$string = str_replace((String)"{name}", (String)$player->getName(), $string);
		$string = str_replace((String)"{money}", (String)$this->dataManager->getPlayerMoney($player), $string);
		$string = str_replace((String)"{coin}", (String)$this->dataManager->getPlayerCoin($player), $string);
		$string = str_replace((String)"{clan}", (String)$this->dataManager->getPlayerClan($player), $string);
		$string = str_replace((String)"{cg}", (String)$this->dataManager->getPlayerCG($player), $string);			
		$string = str_replace((String)"{point}", (String)$this->dataManager->getPlayerPoint($player), $string);
		$string = str_replace((String)"{rbcoin}", (String)$this->dataManager->getPlayerRbcoin($player), $string);
		$string = str_replace((String)"{online}",(String) count($this->getServer()->getOnlinePlayers()), $string);
		$string = str_replace((String)"{max_online}", (String) $this->getServer()->getMaxPlayers(), $string);
		$string = str_replace((String)"{rank}", (String)$this->dataManager->getPlayerRank($player), $string);
		$string = str_replace((String)"{prison_rank}",(String) $this->dataManager->getRankUpRank($player), $string);
		$string = str_replace((String)"{prison_next_rank_price}",(String) $this->dataManager->getRankUpRankPrice($player), $string);
		$string = str_replace((String)"{item_name}", (String) $player->getInventory()->getItemInHand()->getName(), $string);
		$string = str_replace((String)"{item_meta}", (String) $player->getInventory()->getItemInHand()->getMeta(), $string);
		$string = str_replace((String)"{item_id}", (String)$player->getInventory()->getItemInHand()->getId(), $string);
		$string = str_replace((String)"{x}",(String) intval($player->getPosition()->getX()), $string);
		$string = str_replace((String)"{y}",(String) intval($player->getPosition()->getY()), $string);
		$string = str_replace((String)"{z}",(String) intval($player->getPosition()->getZ()), $string);
		$string = str_replace((String)"{faction}", (String)$this->dataManager->getPlayerFaction($player), $string);
		$string = str_replace((String)"{faction_power}",(String) $this->dataManager->getFactionPower($player), $string);
		$string = str_replace((String)"{load}", (String)$this->getServer()->getTickUsage(), $string);
		$string = str_replace((String)"{tps}",(String) $this->getServer()->getTicksPerSecond(), $string);
		$string = str_replace((String)"{world_name}", (String)$player->getWorld()->getDisplayName(), $string);
		$string = str_replace((String)"{world_folder_name}",(String) $player->getWorld()->getFolderName(), $string);
		$string = str_replace((String)"{ip}", (String)$player->getNetworkSession()->getIp(), $string);
		$string = str_replace((String)"{ping}", (String)$player->getNetworkSession()->getPing(), $string);
		$string = str_replace((String)"{kills}", (String)$this->dataManager->getPlayerKills($player), $string);
		$string = str_replace((String)"{deaths}", (String)$this->dataManager->getPlayerDeaths($player), $string);
		$string = str_replace((String)"{kdr}",(String) $this->dataManager->getPlayerKillToDeathRatio($player), $string);
		$string = str_replace((String)"{prefix}", (String)$this->dataManager->getPrefix($player), $string);
		$string = str_replace((String)"{suffix}", (String)$this->dataManager->getSuffix($player), $string);
		$string = str_replace((String)"{cps}",(String) $this->dataManager->getClicks($player), $string);
		$string = str_replace((String)"{is_state}", (String)$this->dataManager->getIsleState($player), $string);
		$string = str_replace((String)"{is_blocks}",(String) $this->dataManager->getIsleBlocks($player), $string);
		$string = str_replace((String)"{is_members}", (String)$this->dataManager->getIsleMembers($player), $string);
		$string = str_replace((String)"{is_size}", (String)$this->dataManager->getIsleSize($player), $string);
		$string = str_replace((String)"{is_rank}", (String)$this->dataManager->getIsleRank($player), $string);

		return $string;
	}

	public function getScoreboards(): ?array{
		return $this->scoreboards;
	}

	public function getScoreboardData(string $world): ?array{
		return !isset($this->scoreboards[$world]) ? null : $this->scoreboards[$world];
	}

	public function getScoreWorlds(): ?array{
		return is_null($this->scoreboards) ? null : array_keys($this->scoreboards);
	}
}