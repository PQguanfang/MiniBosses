# MiniBosses
一个刷怪插件

我只是汉化 原插件2年前更新 最近有人捡坑了 我就拿来分享了 如果有问题 请到捡坑者的目区报告i

# 指令
新建刷怪点: /minibosses create network-id/entityType Name

设置刷怪点的生成点: /minibosses spawn Name

删除刷怪点: /minibosses delete Name

刷怪点列表: /minibosses list

#已支持的生物
Chicken, Cow, Pig, Sheep, Wolf, Villager, Mooshroom, Squid, Rabbit, Bat, IronGolem, SnowGolem, Ocelot, 
Horses, Zombie, Creeper, Skeleton, Spider, pigman, slime, enderman, silverfish, cavespider, ghast, magmacube
, blaze, zombievillager, witch, stray, husk, witherskeleton, wither, enderdragon, shulker, endermite, human

# 配置文件
```
刷怪点名字:
  network-id: 63 刷的生物种类id
  x: 127.444900 #坐标
  "y": 4.000000 #坐标
  z: 160.134600 #坐标
  level: FLAT 所在的世界
  health: 20 #怪物生命值
  range: 10 #怪物到距离刷怪点多远就会自动消失
  attackDamage: 1 #伤害值
  attackRate: 10 #伤害频率
  speed: 1 速度
  drops: 1;0;1;;100 2;0;1;;50 3;0;1;;25 #掉落物，格式: ID;Damage;Count;NBT;DropChance(1-100)
  如果插件报错，这就写物品id：特殊值：数量 后面就不写了
  respawnTime: 100 #刷新时长
  skin: "" #皮肤
  heldItem: "276;36;1;\n\x03\0tag\t\x04\0ench\n\x01\0\0\0\x02\x02\0id\x05\0\x02\x03\0lvl\x01\0\0\0"#in the format: ID;Damage;Count;NBT 手持物品 如果报错 同掉落物
  scale: 1
```

# 许可与版权
Copyright (C) 2016 wolfdale All Rights Reserved.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.
