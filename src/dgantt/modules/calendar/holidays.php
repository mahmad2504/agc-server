<?php

/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
AGC is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with AGC.  If not, see <http://www.gnu.org/licenses/>.
*/

$holidays = array();


echo '[';
	foreach($holidays as $holiday)
	{
		$d = strtotime($holiday);
		$year = date('Y',$d);
		$month = date('m',$d)-1;
		$day = date('j',$d);
		echo 'new Date('.$year.",".$month.",".$day."),";
	}
	echo ']';

/*
echo 'disabledDays: [
            new Date(2017,0,3),
			new Date(2017,0,3),
            new Date(currentYear,1,3),
            new Date(currentYear,1,8),
            new Date(currentYear,1,9),
            new Date(currentYear,1,10),
            new Date(currentYear,1,11),
            new Date(currentYear,1,13),
            new Date(currentYear,1,14),
            new Date(currentYear,1,15)
        ]';
		*/



?>