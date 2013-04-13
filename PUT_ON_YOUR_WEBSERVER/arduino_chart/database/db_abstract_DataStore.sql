-- Project: Arduino simple Chart, Author: Matthias Maderer
-- This file is called from script abstract_DataStore.php

truncate table DataStore_hour;

insert into DataStore_hour (Sensor_ID, DataStore_Value, DataStore_TIME) (select 
    Sensor.Sensor_ID,
	ROUND(AVG(DataStore_VALUE), 2) as val,
    max(DataStore_TIME)
from
    Sensor
        inner join
    DataStore ON (Sensor.Sensor_ID = DataStore.Sensor_ID)
group by Sensor.Sensor_ID,ROUND(UNIX_TIMESTAMP(DataStore_TIME) / (60 * 60))
order by DataStore_TIME,Sensor_ID); 

truncate table DataStore_day;

insert into DataStore_day(Sensor_ID, DataStore_Value, DataStore_TIME) (select 
    Sensor.Sensor_ID,
	ROUND(AVG(DataStore_VALUE), 2) as val,
    max(DataStore_TIME)
from
    Sensor
        inner join
    DataStore ON (Sensor.Sensor_ID = DataStore.Sensor_ID)
group by Sensor.Sensor_ID,ROUND(UNIX_TIMESTAMP(DataStore_TIME) / (1440 * 60))
order by DataStore_TIME,Sensor_ID); 

truncate table DataStore_week;

insert into DataStore_week(Sensor_ID, DataStore_Value, DataStore_TIME) (select 
    Sensor.Sensor_ID,
	ROUND(AVG(DataStore_VALUE), 2) as val,
    max(DataStore_TIME)
from
    Sensor
        inner join
    DataStore ON (Sensor.Sensor_ID = DataStore.Sensor_ID)
group by Sensor.Sensor_ID,ROUND(UNIX_TIMESTAMP(DataStore_TIME) / (10080* 60))
order by DataStore_TIME,Sensor_ID); 