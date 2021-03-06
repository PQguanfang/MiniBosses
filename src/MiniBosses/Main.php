<?php
declare(strict_types=1);
namespace MiniBosses;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener {


	/** @var Config */
	public $data;

	public function onEnable() {
		@mkdir($this->getDataFolder());
		Entity::registerEntity(Boss::class);
		$this->data = new Config($this->getDataFolder()."Bosses.yml", Config::YAML);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) : bool {
		if(!isset($args[0])) {
			$sender->sendMessage("用法: /minibosses create/spawn/delete/list");
		}elseif($args[0] === "create") {
			if(!($sender instanceof Player)) {
				$sender->sendMessage("请在游中使用这个命令");
			}elseif(count($args) >= 3) {
				$networkId = $args[1];
				array_shift($args);
				array_shift($args);
				$name = implode(' ', $args);
				if($this->data->get($name, null) === null) {
					if(is_numeric($networkId) and in_array($networkId, Data::NETWORK_IDS)) {
						$networkId = (int) $networkId;
					}elseif(array_key_exists($networkId, Data::NETWORK_IDS)) {
						$networkId = Data::NETWORK_IDS[strtolower($networkId)];
					}else {
						$sender->sendMessage(TF::RED."Unrecognised Network ID or Entity type $networkId");
						return true;
					}
					$heldItem = $sender->getInventory()->getItemInHand();
					$this->data->set($name, ["network-id" => (int) $networkId, "x" => $sender->x, "y" => $sender->y, "z" => $sender->z, "level" => $sender->level->getName(), "health" => 20, "range" => 10, "attackDamage" => 1, "attackRate" => 10, "speed" => 1, "drops" => "1;0;1;;100 2;0;1;;50 3;0;1;;25", "respawnTime" => 100, "skin" => ($networkId === 63 ? bin2hex($sender->getSkin()->getSkinData()) : ""), "heldItem" => ($heldItem->getId().";".$heldItem->getDamage().";".$heldItem->getCount().";".$heldItem->getNamedTag()->toString()), "scale" => 1]);
					$this->data->save();
					$this->spawnBoss($name);
					$sender->sendMessage(TF::GREEN."成功新增刷怪点: $name ，请继设置生成点");
				}else $sender->sendMessage(TF::RED."此名字的刷怪点已存在!");
			}else $sender->sendMessage(TF::RED."正用用法: /minibosses create network-id name");
		}elseif($args[0] === "spawn") {
			if(count($args) >= 2) {
				array_shift($args);
				$name = implode(' ', $args);
				if($this->data->get($name, null) !== null) {
					$ret = $this->spawnBoss($name);
					if($ret === true) {
						$sender->sendMessage("成功设置 $name 的生成点");
					}else $sender->sendMessage(TF::RED."在设置 $name 的生成点时出现错误 : $ret");
				}else $sender->sendMessage(TF::RED."此刷怪点不存在!");
			}else $sender->sendMessage(TF::RED."正确用法: /minibosses spawn name");
		}elseif($args[0] === "delete") {
			if(count($args) >= 2) {
				array_shift($args);
				$name = implode($args);
				if(($data = $this->data->get($name, null)) !== null) {
					if($this->getServer()->loadLevel($data["level"])) {
						$l = $this->getServer()->getLevelByName($data["level"]);
						if($chunk = $l->getChunk($data["x"] >> 4, $data["z"] >> 4)) {
							foreach($chunk->getEntities() as $e) {
								if($e instanceof Boss and $e->getNameTag() === $name) {
									$e->close();
								}
							}
						}
					}
					$this->data->remove($name);
					$this->data->save();
					$sender->sendMessage(TF::GREEN."成功删除刷怪点: $name");
				}else $sender->sendMessage(TF::RED."此刷怪点不存在!");
			}else $sender->sendMessage(TF::RED."正确用法: /minibosses delete name");
		}elseif($args[0] === "list") {
			$sender->sendMessage(TF::GREEN."----MiniBosses 刷怪插件----");
			$sender->sendMessage(implode(', ', array_keys($this->data->getAll())));
		}else {
			$sender->sendMessage(TF::RED."用法: /minibosses create/spawn/delete/list");
		}
		return true;
	}

	public function spawnBoss(string $name = "Boss") {
		$data = $this->data->get($name);
		if(!$data) {
			return "No data, Boss does not exist";
		}elseif(!$this->getServer()->loadLevel($data["level"])) {
			return "Failed to load Level {$data["level"]}";
		}
		$networkId = (int) $data["network-id"];
		$pos = new Position($data["x"], $data["y"], $data["z"], $this->getServer()->getLevelByName($data["level"]));
		$health = $data["health"];
		$range = $data["health"];
		$attackDamage = $data["attackDamage"];
		$attackRate = $data["attackRate"];
		$speed = $data["speed"];
		$drops = $data["drops"];
		$respawnTime = $data["respawnTime"];
		$skin = ($networkId === 63 ? $data["skin"] : "");
		$heldItem = $data["heldItem"];
		$scale = $data["scale"] ?? 1;
		$nbt = Boss::createBaseNBT($pos);
		$nbt->setTag(new ListTag("spawnPos", [new DoubleTag("", $pos->x), new DoubleTag("", $pos->y), new DoubleTag("", $pos->z)]));
		$nbt->setFloat("range", $range * $range);
		$nbt->setFloat("attackDamage", $attackDamage);
		$nbt->setInt("networkId", $networkId);
		$nbt->setInt("attackRate", $attackRate);
		$nbt->setFloat("speed", $speed);
		$nbt->setString("drops", $drops);
		$nbt->setInt("respawnTime", $respawnTime);
		$nbt->setString("skin", $skin);
		$nbt->setString("heldItem", $heldItem);
		$nbt->setFloat("scale", $scale);
		$ent = Entity::createEntity("Boss", $pos->level, $nbt);
		$ent->setMaxHealth($health);
		$ent->setHealth($health);
		$ent->setNameTag($name);
		$ent->setNameTagAlwaysVisible(true);
		$ent->setNameTagVisible(true);
		$ent->spawnToAll();
		return true;
	}

	public function respawn($name, $time) {
		if($this->data->get($name)) {
			$this->getScheduler()->scheduleDelayedTask(new class($this, $name) extends Task {

				/** @var Main */
				private $plugin;
				/** @var string */
				private $name;

				public function __construct(Main $plugin, $name) {
					$this->plugin = $plugin;
					$this->name = $name;
				}

				public function onRun($currentTick) {
					$this->plugin->spawnBoss($this->name);
				}
			}, $time);
		}
	}
}
