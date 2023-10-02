<?php

$dev1instance = "i-054ee3b5cbcce9710";
$dev2instance = "i-00021f9572af5111c";

shell_exec("aws ec2 stop-instances --instance-ids $dev1instance --profile produser --region us-east-2");
sleep(1);
shell_exec("aws ec2 stop-instances --instance-ids $dev2instance --profile produser --region us-east-2");