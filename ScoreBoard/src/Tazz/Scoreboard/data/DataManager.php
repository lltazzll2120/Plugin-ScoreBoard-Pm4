<?php
declare(strict_types = 1);

#Author: Tazz

namespace Tazz\ScoreHud\data;

use _64FF00\PurePerms\PurePerms;
use FactionsPro\FactionMain;
use DaPigGuy\PiggyFactions\players\PlayerManager;
use Tazz\CPS\CPS;
use Tazz\KDR\KDR;
use Tazz\ScoreHud\Main;
use onebone\economyapi\EconomyAPI;
use onebone\pointapi\PointAPI;
use onebone\rebirthcoinapi\RebirthCoinAPI;
use pocketmine\player\Player;
use rankup\rank\Rank;
use rankup\RankUp;
use room17\SkyBlock\session\BaseSession as SkyBlockSession;
use room17\SkyBlock\SkyBlock;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

class DataManager{
	
	/** @var Main */
	private $plugin;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}
	
	/**
	 * @param Player $player
	 * @return float|string
	 */
	public function getPlayerMoney(Player $player){
		/** @var EconomyAPI $economyAPI */
		$economyAPI = $this->plugin->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		if($economyAPI instanceof EconomyAPI){
        	$moneys = (Float) $economyAPI->myMoney($player); 
             $money = number_format($moneys, 0, ',', '.');
			return $money;
		}else{
			return "Plugin not found";
		}
	}
	
	public function getPlayerCoin(Player $player){
		$coinAPI = $this->plugin->getServer()->getPluginManager()->getPlugin("CoinAPI");
		if(!$coinAPI == null){
        	$coins = (Float) $coinAPI->myCoin($player); 
             $coin = number_format($coins, 0, ',', '.');
			return $coin;
		}else{
			return "Plugin not found";
		}
	}
	
	public function getPlayerClan(Player $player){
		$clanapi = $this->plugin->getServer()->getPluginManager()->getPlugin("BedrockClans");
		if(!$clanapi == null){
		    $p = $clanapi->getPlayer($player);
            if ($p->isInClan()){
                $clan = $p->getClan();
                return $clan->getDisplayName();
            }else{
			   return "No Clan";
            }
		}else{
			return "Plugin not found";
		}
	}
	
	public function getPlayerCG(Player $player){
		$rpg = $this->plugin->getServer()->getPluginManager()->getPlugin("RPG");
		if(!$rpg == null){
            return $rpg->canhgioi($player);
		}else{
			return "Plugin not found";
		}
	}	
	
	
	/**
	 * @param Player $player
	 * @return float|string
	 */
	public function getPlayerPoint(Player $player){
		/** @var PointAPI $pointAPI */
		$pointAPI = $this->plugin->getServer()->getPluginManager()->getPlugin("PointAPI");
		if($pointAPI instanceof PointAPI){
             $points = (Float) $pointAPI->myPoint($player);	    
             $point = number_format($points, 0, ',', '.');		    
			return $point;
		}else{
			return "Plugin not found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return float|string
	 */
	public function getPlayerRbcoin(Player $player){
		/** @var RebirthCoinAPI $rebirthcoinAPI */
		$rebirthcoinAPI = $this->plugin->getServer()->getPluginManager()->getPlugin("RebirthCoinAPI");
		if($rebirthcoinAPI instanceof RebirthCoinAPI){
			return $rebirthcoinAPI->myRebirthCoin($player);
		}else{
			return "Plugin not found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getPlayerRank(Player $player): string{
		/** @var PurePerms $purePerms */
		$purePerms = $this->plugin->getServer()->getPluginManager()->getPlugin("PurePerms");
		if($purePerms instanceof PurePerms){
			$group = $purePerms->getUserDataMgr()->getData($player)['group'];
			if($group !== null){
				return $group;
			}else{
				return "No Rank";
			}
		}else{
			return "Plugin not found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return bool|int|string
	 */
	public function getRankUpRank(Player $player){
		/** @var RankUp $rankUp */
		$rankUp = $this->plugin->getServer()->getPluginManager()->getPlugin("RankUp");
		if($rankUp instanceof RankUp){
			$group = $rankUp->getRankUpDoesGroups()->getPlayerGroup($player);
			if($group !== false){
				return $group;
			}else{
				return "No Rank";
			}
		}
		return "Plugin not found";
	}

	/**
	 * @param Player $player
	 * @return bool|Rank|string
	 */
	public function getRankUpRankPrice(Player $player){
		/** @var RankUp $rankUp */
		$rankUp = $this->plugin->getServer()->getPluginManager()->getPlugin("RankUp");
		if($rankUp instanceof RankUp){
			$nextRank = $rankUp->getRankStore()->getNextRank($player);
			if($nextRank !== false){
				return $nextRank->getPrice();
			}else{
				return "Max Rank";
			}
		}
		return "Plugin not found";
	}
    
    
	/**
	 * @param Player $player
	 * @return string
	 */
    public static function getPlayerFaction(Player $player): string {
        $member = PlayerManager::getInstance()->getPlayer($player);
        $faction = $member === null ? null : $member->getFaction();
        if (!is_null($faction)) {
            return $faction->getName();
        } else return "...";
    }
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getFactionPower(Player $player){
		/** @var FactionMain $factionsPro */
		$factionsPro = $this->plugin->getServer()->getPluginManager()->getPlugin("FactionsPro");
		if($factionsPro instanceof FactionMain){
			$factionName = $factionsPro->getPlayerFaction($player->getName());
			if($factionName === null){
				return "Chưa gia nhập";
			}
			return $factionsPro->getFactionPower($factionName);
		}
		return "Plugin not found";
	}
	
	/**
	 * @param Player $player
	 * @return int|string
	 */
	public function getPlayerKills(Player $player){
		/** @var KDR $kdr */
		$kdr = $this->plugin->getServer()->getPluginManager()->getPlugin("KDR");
		if($kdr instanceof KDR){
			return $kdr->getProvider()->getPlayerKillPoints($player);
		}else{
			return "Plugin Not Found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return int|string
	 */
	public function getPlayerDeaths(Player $player){
		/** @var KDR $kdr */
		$kdr = $this->plugin->getServer()->getPluginManager()->getPlugin("KDR");
		if($kdr instanceof KDR){
			return $kdr->getProvider()->getPlayerDeathPoints($player);
		}else{
			return "Plugin Not Found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getPlayerKillToDeathRatio(Player $player): string{
		/** @var KDR $kdr */
		$kdr = $this->plugin->getServer()->getPluginManager()->getPlugin("KDR");
		if($kdr instanceof KDR){
			return $kdr->getProvider()->getKillToDeathRatio($player);
		}else{
			return "Plugin Not Found";
		}
	}
	
	/**
	 * @param Player $player
	 * @param null   $levelName
	 * @return string
	 */
	public function getPrefix(Player $player, $levelName = null): string{
		/** @var PurePerms $purePerms */
		$purePerms = $this->plugin->getServer()->getPluginManager()->getPlugin("PurePerms");
		if($purePerms instanceof PurePerms){
			$prefix = $purePerms->getUserDataMgr()->getNode($player, "prefix");
			if($levelName === null){
				if(($prefix === null) || ($prefix === "")){
					return "No Prefix";
				}
				return (string) $prefix;
			}else{
				$worldData = $purePerms->getUserDataMgr()->getWorldData($player, $levelName);
				if(empty($worldData["prefix"]) || $worldData["prefix"] == null){
					return "No Prefix";
				}
				return $worldData["prefix"];
			}
		}else{
			return "Plugin not found";
		}
	}
	
	/**
	 * @param Player $player
	 * @param null   $levelName
	 * @return string
	 */
	public function getSuffix(Player $player, $levelName = null): string{
		/** @var PurePerms $purePerms */
		$purePerms = $this->plugin->getServer()->getPluginManager()->getPlugin("PurePerms");
		if($purePerms instanceof PurePerms){
			$suffix = $purePerms->getUserDataMgr()->getNode($player, "suffix");
			if($levelName === null){
				if(($suffix === null) || ($suffix === "")){
					return "No Suffix";
				}
				return (string) $suffix;
			}else{
				$worldData = $purePerms->getUserDataMgr()->getWorldData($player, $levelName);
				if(empty($worldData["suffix"]) || $worldData["suffix"] == null){
					return "No Suffix";
				}
				return $worldData["suffix"];
			}
		}else{
			return "Plugin not found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return int|string
	 */
	public function getClicks(Player $player){
		/** @var CPS $cps */
		$cps = $this->plugin->getServer()->getPluginManager()->getPlugin("CPS");
		if($cps instanceof CPS){
			return $cps->getClicks($player);
		}else{
			return "Plugin Not Found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return int|string
	 */
	public function getIsleBlocks(Player $player){
		/** @var SkyBlock $sb */
		$sb = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if($sb instanceof SkyBlock){
			$session = $sb->getSessionManager()->getSession($player);
			if((is_null($session)) || (!$session->hasIsle())){
				return "No Island";
			}
			$isle = $session->getIsle();
			return $isle->getBlocksBuilt();
		}else{
			return "Plugin Not Found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getIsleSize(Player $player){
		/** @var SkyBlock $sb */
		$sb = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if($sb instanceof SkyBlock){
			$session = $sb->getSessionManager()->getSession($player);
			if((is_null($session)) || (!$session->hasIsle())){
				return "No Island";
			}
			$isle = $session->getIsle();
			return $isle->getCategory();
		}else{
			return "Plugin Not Found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return int|string
	 */
	public function getIsleMembers(Player $player){
		/** @var SkyBlock $sb */
		$sb = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if($sb instanceof SkyBlock){
			$session = $sb->getSessionManager()->getSession($player);
			if((is_null($session)) || (!$session->hasIsle())){
				return "No Island";
			}
			$isle = $session->getIsle();
			return count($isle->getMembers());
		}else{
			return "Plugin Not Found";
		}
	}
	
	/**
	 * @param Player $player
	 * @return string
	 */
	public function getIsleState(Player $player){
		/** @var SkyBlock $sb */
		$sb = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if($sb instanceof SkyBlock){
			$session = $sb->getSessionManager()->getSession($player);
			if((is_null($session)) || (!$session->hasIsle())){
				return "No Island";
			}
			$isle = $session->getIsle();
			return $isle->isLocked() ? "Locked" : "Unlocked";
		}else{
			return "Plugin Not Found";
		}
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public function getIsleRank(Player $player){
		/** @var SkyBlock $sb */
		$sb = $this->plugin->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if($sb instanceof SkyBlock){
			$session = $sb->getSessionManager()->getSession($player);
			if((is_null($session)) || (!$session->hasIsle())){
				return "No Island";
			}
			switch($session->getRank()){
				case SkyBlockSession::RANK_DEFAULT:
					return "Member";
				case SkyBlockSession::RANK_OFFICER:
					return "Officer";
				case SkyBlockSession::RANK_LEADER:
					return "Leader";
				case SkyBlockSession::RANK_FOUNDER:
					return "Founder";
			}
			return "No Rank";
		}else{
			return "Plugin Not Found";
		}
	}
}
