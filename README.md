这个是php版本的snowflake唯一ID生成器，可供大家使用！


ID 生成策略
毫秒级时间41位+机器ID 10位+毫秒内序列12位。
0           41     51     64
+-----------+------+------+
|time       |pc    |inc   |
+-----------+------+------+
前41bits是以微秒为单位的timestamp。
接着10bits是事先配置好的机器ID。
最后12bits是累加计数器。
macheine id(10bits)标明最多只能有1024台机器同时产生ID，sequence number(12bits)也标明1台机器1ms中最多产生4096个ID，