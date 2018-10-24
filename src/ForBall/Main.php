<?php 

namespace ForBall;


//      _________  ___  ___  ___  ___      ___    ___ 
//     |\___   ___\\  \|\  \|\  \|\  \    |\  \  /  /|
//     \|___ \  \_\ \  \ \  \\\  \ \  \   \ \  \/  / /
//          \ \  \ \ \  \ \   __  \ \  \   \ \    / / 
//           \ \  \ \ \  \ \  \ \  \ \  \   \/  /  /  
//            \ \__\ \ \__\ \__\ \__\ \__\__/  / /    
//             \|__|  \|__|\|__|\|__|\|__|\___/ /     
//                                       \|___|/       


use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Snowball;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\inventory\BaseInventory;

class Main extends PluginBase implements Listener 
{
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if($this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		$this->checkLevel('4b');
	}

	private function spawnPlayer($player, $status)
	{
		if($status)
		{
			$rnd = mt_rand(0, 4);
			$arr0 = array(-13, 155, 149);
			$arr1 = array(-14, 155, 175);
			$arr2 = array(-11, 166, 142);
			$arr3 = array(-31, 161, 190);
			$arr4 = array(-4, 155, 183);
			switch($rnd)
			{
				case 0:
					$arr = $arr0;
					break;
				case 1:
					$arr = $arr1;
					break;
				case 2:
					$arr = $arr2;
					break;
				case 3:
					$arr = $arr3;
					break;
				case 4:
					$arr = $arr4;
					break;
			}
			$player->teleport(new Position($arr[0], $arr[1], $arr[2], $this->getServer()->getLevelByName('4b')));
		}
		if (!$status) 
		{
			$player->teleport(new Position($this->getServer()->getDefaultLevel()->getSpawnLocation()->getX(), $this->getServer()->getDefaultLevel()->getSpawnLocation()->getY(), $this->getServer()->getDefaultLevel()->getSpawnLocation()->getZ(), $this->getServer()->getDefaultLevel()));
		}
	}

	private function checkLevel($w) 
	{
		if (!$this->getServer()->isLevelGenerated($w)) return null;
		if (!$this->getServer()->isLevelLoaded($w)) 
		{
			if (!$this->getServer()->loadLevel($w)) return null;
		}
		return $this->getServer()->getLevelByName($w);
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
        if($label == '4b')
	    {
			$p = $sender->getPlayer();
			if ($p->getLevel() != $this->getServer()->getLevelByName('4b'))
			{
				$this->spawnPlayer($p, TRUE);
				$p->sendMessage("§eЗагрузка мира 4Ball чтобы выйти пишите /4b, чтобы выйти в хаб пишите после этого /hub");
				$this->ItemsMemory($p, TRUE);
				$this->getItems($p);
			}
		    else 
			{
            	$this->spawnPlayer($p, FALSE);
            	$this->ItemsMemory($p, FALSE);
            	$p->sendMessage("§eВыход из игры 4Ball");
            	$p->sendMessage("§eПолучено денег за игру $" . $this->FBMemory[$p->getName()]['money']); 
            	unset($this->FBMemory[$p->getName()]);
			}
		}
	} 

	public function getItems($player) 
	{ 
		$player->getInventory()->addItem(Item::get(294, 0, 1));
		$player->getInventory()->addItem(Item::get(332, 0, 128));
	}

	public function ItemsMemory($player, $status) 
	{ 
		if($status)
		{
			$this->FBMemory[$player->getName()]['money'] = 0;
			$this->FBMemory[$player->getName()]['items'] = $player->getInventory()->getContents();
			$player->getInventory()->clearAll();
			$this->FBMemory[$player->getName()]['armor']['helmet'] = $player->getInventory()->getHelmet();
			$this->FBMemory[$player->getName()]['armor']['chestplate'] = $player->getInventory()->getChestplate();
			$this->FBMemory[$player->getName()]['armor']['leggings'] = $player->getInventory()->getLeggings();
			$this->FBMemory[$player->getName()]['armor']['boots'] = $player->getInventory()->getBoots();
			$player->getInventory()->setHelmet(Item::get(0, 0, 0));
			$player->getInventory()->setChestplate(Item::get(0, 0, 0));
			$player->getInventory()->setLeggings(Item::get(0, 0, 0));
			$player->getInventory()->setBoots(Item::get(0, 0, 0));
		}

		if(!$status)
		{
			$player->getInventory()->clearAll();
			$player->getInventory()->setContents($this->FBMemory[$player->getName()]['items']);
			$player->getInventory()->setHelmet($this->FBMemory[$player->getName()]['armor']['helmet']);
			$player->getInventory()->setChestplate($this->FBMemory[$player->getName()]['armor']['chestplate']);
			$player->getInventory()->setLeggings($this->FBMemory[$player->getName()]['armor']['leggings']);
			$player->getInventory()->setBoots($this->FBMemory[$player->getName()]['armor']['boots']);
		}
	}

	public function onDamage(EntityDamageEvent $event)
	{
        $p = $event->getEntity();
		if($p->getLevel() == $this->getServer()->getLevelByName('4b'))
		{
	    	if ($event instanceof EntityDamageByEntityEvent) 
			{
				if($event->getCause() == EntityDamageEvent::CAUSE_PROJECTILE)
				{
					$event->setDamage(5);
				}

				$damager = $event->getDamager();
				if($p instanceof Player)
				{
					 if(round($event->getFinalDamage()) >= $p->getHealth() and $event->getCause() == EntityDamageEvent::CAUSE_PROJECTILE or $damager->getInventory()->getItemInHand()->getId() == 294)
					 	{
							$event->setCancelled(true);
		                	$p->setHealth(20);
		                	$this->spawnPlayer($p, TRUE);
		                	$p->sendTitle(TextFormat::RED.'УБИТ');
							$this->getItems($p);
		                	$damager->sendTitle(TextFormat::RED . 'Убил +50$');
		                	$this->FBMemory[$damager->getName()]['money']+= 50;
		                	$this->economy->addMoney($damager, 50);
						}
				}
				if ($event->getCause() != EntityDamageEvent::CAUSE_PROJECTILE and $damager->getInventory()->getItemInHand()->getId() != 294) 
				{
					$event->setCancelled(true);
				} 
    		}
		}
	}

	public function exitPlayer(PlayerQuitEvent $event)
	{
		unset($this->FBMemory[$event->getPlayer()->getName()]);
	}

	public function blockCMD(PlayerCommandPreprocessEvent $event)
	{
        if (isset($this->FBMemory[$event->getPlayer()->getName()]))
		{
			if($this->getServer()->getLevelByName("4b") == $event->getPlayer()->getLevel())
			{
				$cmd = explode(" ", $event->getMessage());
				if($cmd[0] != "/4b" and $cmd[0][0] == "/") 
				{
					$event->getPlayer()->sendMessage("Нельзя использовать комманды");
				}
			}
		}
	}
	
}
?>