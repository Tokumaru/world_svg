<?php // same as 18 but maps resources
// 0 // run the simulation
function Simulation($title,$max){
	$board = BuildGame($title,$max); // called once; variables do not change
	//echo $board['title'] .' '. $board['highscore'] .' '. $board['max'] . ' '. $board['reference']['era'][8] .' ';
	//echo 'Test game with AI set to randomly research techs and bid on ters by value. ';
	$board = ImportTers($board); // adds each ter from excel
	$board = AddCivs($board); // adds an NPC for each ter
	$game = PlayGame($board,$max); // recursive
	
	$winciv = $game['civs'][$game['winner']];
	//foreach($winciv['technology'] as $tech=>$details){
		//if($details[0]=='have' and 'string'==gettype($tech)){echo ', '.$tech;} // string check for void fix
	//}
	echo ' and this: <br>';
	$display = DisplayMaps($game);
	return $display;
} 

// 1 // build the initial board
function BuildGame($title,$max){
	$board = array(
	'title' => $title,
	'round' => 0,
	'highscore' => 0,
	'continue' => true,	
	'max' => $max,
	'techDone' => false,
	'winner' => 'none'
	);
	// tech and geo effects are implemented elsewhere
	$terrainDict = array('ocean'=>'navy','sea'=>'teal','tundra'=>'silver','plain'=>'olive','grass'=>'green','hill'=>'maroon','river'=>'aqua','flood'=>'purple','desert'=>'yellow','mountain'=>'black');
	$featureDict = array('-'=>'gray','taiga'=>'maroon','ice'=>'aqua','wood'=>'green','jungle'=>'lime','aridification'=>'red');
	$resourceDict = array('-'=>'gray','alcohol'=>'purple','coal'=>'olive','copper'=>'darkorange','drugs'=>'green','fur'=>'firebrick','gems'=>'yellow','gold'=>'gold','incense'=>'yellowgreen','iron'=>'indigo','oil'=>'lightblue','silk'=>'sienna','spice'=>'lightcoral','sugar'=>'red','textiles'=>'deeppink','tin'=>'skyblue','uranium'=>'lime');
	$geoDict = array('terrain'=>$terrainDict,'feature'=>$featureDict,'resource'=>$resourceDict);
	$eraDict = array(0,1,1,2,3,5,8,13,21,34);
	$incomeDict = array('#000000','#500000','#A00000','#F00000','orange','#FFFF00','#00FF00','#00FFFF','#0000FF','purple');
	//$upgradeDict = array('pink','#F00000','orange','#FFFF00','#00FF00','#00FFFF','#0000FF','purple','olive');
	//$upgradeDict = array('#000000','#100000','#200000','#300000','#400000','#500000','#600000','#700000','#800000','#900000','#A00000','#B00000','#C00000','#D00000','#E00000','#F00000','orange','#FFFF00','#00FF00','#00FFFF','#0000FF','violet');
	$upgradeDict = array('navy','blue','green','teal','deepskyblue','springgreen','lime','aqua','dodgerblue','olive','lawngreen','purple','darkred','red','sienna','hotpink','pink','orange','yellow','lightsalmon','white','red','yellow','green','blue','purple');
	$checkDict = array('pink'=>0,'#F00000'=>0,'orange'=>0,'#FFFF00'=>0,'#00FF00'=>0,'#00FFFF'=>0,'#0000FF'=>0,'purple'=>0,'olive'=>0);
	$techDict = array('writing'=>array(),'agriculture'=>array(),'wheel'=>array(),'smelting'=>array(),
	'arithmetic'=>array('writing'),'husbandry'=>array('agriculture'),'ceramics'=>array('wheel'),'alloying'=>array('smelting'),
	'geometry'=>array('arithmetic'),'plumbing'=>array('arithmetic'),'forestry'=>array('alloying'),'furnace'=>array('alloying'),
	'philosophy'=>array('writing'),'alchemy'=>array('arithmetic','ceramics','alloying'),'concrete'=>array('geometry','ceramics'),'casting'=>array('ceramics','furnace'),
	'paper'=>array('geometry','alchemy','alloying'),'fertilizer'=>array('alchemy','husbandry'),'ships'=>array('husbandry','forestry'),'firearm'=>array('forestry','casting','alchemy'),
	'science'=>array('philosophy','paper'),'rotation'=>array('philosophy','agriculture'),'machinery'=>array('paper','casting'),'steel'=>array('furnace','alchemy'),
	'electricity'=>array('science','plumbing','furnace'),'chemistry'=>array('science','fertilizer','firearm'),'engine'=>array('ships','machinery','steel'),'factory'=>array('concrete','machinery','steel'),
	'computing'=>array('electricity','factory'),'genetics'=>array('chemistry','rotation'),'flight'=>array('engine'),'nuclear'=>array('chemistry','electricity'),
	'quantum'=>array('nuclear','computing'),'potency'=>array('genetics'),'nanotech'=>array('computing'),'laser'=>array('nuclear'));
	$techCostDict = array('writing'=>1,'agriculture'=>1,'wheel'=>1,'smelting'=>1,
	'arithmetic'=>1,'husbandry'=>1,'ceramics'=>1,'alloying'=>1,
	'geometry'=>2,'plumbing'=>2,'forestry'=>2,'furnace'=>2,
	'philosophy'=>3,'alchemy'=>3,'concrete'=>3,'casting'=>3,
	'paper'=>5,'fertilizer'=>5,'ships'=>5,'firearm'=>5,
	'science'=>8,'rotation'=>8,'machinery'=>8,'steel'=>8,
	'electricity'=>13,'chemistry'=>13,'engine'=>13,'factory'=>13,
	'computing'=>21,'genetics'=>21,'flight'=>21,'nuclear'=>21,
	'quantum'=>34,'potency'=>34,'nanotech'=>34,'laser'=>34,
	'void'=>999999999); // not expected to be used, just here to use as a placeholder for research options not deleted
	$techCostDict2 = array('writing'=>1,'agriculture'=>1,'wheel'=>1,'smelting'=>1,
	'arithmetic'=>1,'husbandry'=>1,'ceramics'=>1,'alloying'=>1,
	'geometry'=>1,'plumbing'=>1,'forestry'=>1,'furnace'=>1,
	'philosophy'=>1,'alchemy'=>1,'concrete'=>1,'casting'=>1,
	'paper'=>1,'fertilizer'=>1,'ships'=>1,'firearm'=>1,
	'science'=>1,'rotation'=>1,'machinery'=>1,'steel'=>1,
	'electricity'=>1,'chemistry'=>1,'engine'=>1,'factory'=>1,
	'computing'=>1,'genetics'=>1,'flight'=>1,'nuclear'=>1,
	'quantum'=>1,'potency'=>1,'nanotech'=>1,'laser'=>1,
	'void'=>999999999);
	$techCostDict3 = array('writing'=>1,'agriculture'=>1,'wheel'=>1,'smelting'=>1,
	'arithmetic'=>2,'husbandry'=>2,'ceramics'=>2,'alloying'=>2,
	'geometry'=>4,'plumbing'=>4,'forestry'=>4,'furnace'=>4,
	'philosophy'=>8,'alchemy'=>8,'concrete'=>8,'casting'=>8,
	'paper'=>16,'fertilizer'=>16,'ships'=>16,'firearm'=>16,
	'science'=>32,'rotation'=>32,'machinery'=>32,'steel'=>32,
	'electricity'=>64,'chemistry'=>64,'engine'=>64,'factory'=>64,
	'computing'=>128,'genetics'=>128,'flight'=>128,'nuclear'=>128,
	'quantum'=>256,'potency'=>256,'nanotech'=>256,'laser'=>256,
	'void'=>999999999);
	$modernDict = array('canada'=>array('yellowknife','vancouver','calgary','winnipeg','toronto','montreal','halifax','saint_johns'),
	'united_states' => array('anchorage','san_francisco','seattle','oklahoma_city','saint_paul','los_angeles','denver','phoenix','charlotte','chicago','houston','new_orleans','indianapolis','jacksonville','washington_dc','philadelphia','new_york','boston','honolulu'),
	'mexico' => array('tijuana','chihuahua','mexico_city','ecpatec','merida','puebla'),
	'cuba' => array('havana'),
	'haiti' => array('port_au_prince'),
	'dominican' => array('santo_domingo'),
	'guatemala' => array('guatemala'),
	'panama' => array('panama'),
	'colombia' => array('bogata'),
	'venezuela' => array('caracas'),
	'suriname' => array('paramaribo'),
	'ecuador' => array('quito'),
	'peru' => array('lima','cuzco'),
	'chile' => array('santiago'),
	'argentina' => array('buenos_aires','neuquen'),
	'bolivia' => array('la_paz'),
	'uraguay' => array('montevideo'),
	'brazil' => array('belem','manaus','brasilia','fortaleza','salvador','belo_horizonte','sao_paulo','rio_de_janeiro'),
	'iceland' => array('reykjavik'),
	'united_kingdom' => array('edinburgh','manchester','cardiff','london','cordoba','belfast'),
	'ireland' => array('dublin'),
	'norway' => array('oslo'),
	'sweden' => array('stockholm'),
	'finland' => array('helsinki'),
	'denmark' => array('copenhagen'),
	'portugal' => array('lisbon'),
	'spain' => array('madrid','bilbao','barcelona'),
	'france' => array('nantes','picardy','paris','strasbough','bordeaux','marseilles'),
	'netherlands' => array('amsterdam'),
	'belgium' => array('brussels'),
	'germany' => array('hamburg','frankfurt','berlin','cologne','munich'),
	'austria' => array('vienna'),
	'switzerland' => array('zurich'),
	'italy' => array('milan','florence','venice','rome','naples'),
	'poland' => array('g_dansk','wronclaw','warsaw','krakow'),
	'czech_republic' => array('prague'),
	'slovakia' => array('bratislavia'),
	'hungary' => array('budapest'),
	'croatia' => array('zagreb'),
	'serbia' => array('belgrade'),
	'bosnia' => array('sarajevo'),
	'albania' => array('tirana'),
	'greece' => array('athens'),
	'latvia' => array('riga'),
	'lithuania' => array('vilnius'),
	'belarus' => array('minsk'),
	'ukraine' => array('kiev','moldova','sevestapol'),
	'romania' => array('bucharest'),
	'bulgaria' => array('sofia'),
	'turkey' => array('constantinople','izmir','sinope','ankara','konya'),
	'russia' => array('saint_petersburg','arkhangelsk','moscow','ryazan','volgograd','astrakhan','kazan','yekaterinburg','novosibirsk','siberia','yakutsk','vladivostok'),
	'morocco' => array('fes'),
	'algeria' => array('algeirs'),
	'tunisia' => array('tunis'),
	'libya' => array('benghazi','tripoli'),
	'mauritania' => array('west_sahara'),
	'liberia' => array('monrovia'),
	'mali' => array('timbuktu'),
	'ghana' => array('accra'),
	'nigeria' => array('lagos','ngazargamu'),
	'chad' => array('njimi'),
	'cameroon' => array('douala'),
	'congo' => array('kinsasha'),
	'angola' => array('luanda'),
	'south_africa' => array('cape_town','johanesburg','durban'),
	'egypt' => array('alexandria','suez','cairo','aswan'),
	'sudan' => array('al-fashir','khartoum'),
	'south_sudan' => array('juba'),
	'ethiopia' => array('addis_ababa'),
	'somalia' => array('mogadishu'),
	'kenya' => array('nairobi'),
	'uganda' => array('kampala'),
	'botswana' => array('harare'),
	'mozambique' => array('quelimane'),
	'madagascar' => array('antananarivo'),
	'armenia' => array('yerevan'),
	'azerbaijan' => array('baku'),
	'syria' => array('aleppo','damascus'),
	'lebanon' => array('tyre'),
	'israel' => array('jerusalem'),
	'jordan' => array('petra'),
	'saudi_arabia' => array('al_medinah','mecca','arabian_desert','riyadh','khali_desert'),
	'yemen' => array('sana_a'),
	'oman' => array('muscat'),
	'iraq' => array('mosul','baghdad'),
	'kuwait' => array('kuwait'),
	'iran' => array('tabriz','tehran','susa','isfahan'),
	'khazakstan' => array('astana','almaty'),
	'uzbekistan' => array('samarkand'),
	'afghanistan' => array('kabul'),
	'pakistan' => array('quetta','karachi','lahore'),
	'india' => array('delhi','ahmedabad','lucknow','mumbai','chennai','kolkata','hyderbad','jaipur','bangalore','bhopal','patna'),
	'sri_lanka' => array('colombo'),
	'bangladesh' => array('dhaka'),
	'mongolia' => array('ulan_bator'),
	'china' => array('urumqi','lanzhou','lhasa','beijing','jinan','shenyang','xi_an','wuhan','chengdu','shanghai','hangzhou','shantou','hong_kong','guangzhou','hohhot','harbin','changchun','kaifeng','dali','nanjing'),
	'north_korea' => array('pyongyang'),
	'south_korea' => array('seoul','busan'),
	'taiwan' => array('taipei'),
	'japan' => array('asahikawa','sapporo','kyoto','tokyo','fukuoka','okinawa','hiroshima'),
	'burma' => array('yangon'),
	'thailand' => array('bangkok'),
	'vietnam' => array('hanoi','saigon'),
	'singapore' => array('singapore'),
	'philippines' => array('manila','davao'),
	'indonesia' => array('medan','brunei','jakarta','surabaya','moresby'),
	'australia' => array('perth','darwin','brisbane','sydney','melbourne'),
	'new_zealand' => array('wellington'),
	'tuvalu' => array('suva'),
	);
	$board['reference'] = array('modern'=>$modernDict,'tech'=>$techDict,'techCost'=>$techCostDict,'geo'=>$geoDict,'era'=>$eraDict,'income'=>$incomeDict,'upgrade'=>$upgradeDict,'check'=>$checkDict);
	$board['ters'] = array();
	$board['civs'] = array();
	$board['civcount'] = 0;
	return $board;
}

// 2 // import the excel file and use it to sbuild the game state
function ImportTers($board){
	$dom = DOMDocument::load('world_table_2016.xml'); // imports the excel file stored on the server
    $rows = $dom->getElementsByTagName( 'Row' );
    $id_row = true;
    foreach ($rows as $row){ // iterate through rows of excel
		$index = 1;
		$cells = $row->getElementsByTagName( 'Cell' );
		foreach( $cells as $cell ){ // iterate through columns of excel
			$ind = $cell->getAttribute( 'Index' ); // may not be needed
			if ( $ind != null ) $index = $ind; 
			if ( $index == 1 ) $id = $cell->nodeValue; // define the parameters from excel to send to add_ter
			if ( $index == 2 ) $adjacent = $cell->nodeValue;
			if ( $index == 3 ) $name = $cell->nodeValue;
			if ( $index == 4 ) $coast = $cell->nodeValue;
			if ( $index == 5 ) $terrain = $cell->nodeValue;
			if ( $index == 6 ) $feature = $cell->nodeValue;
			if ( $index == 7 ) $resource = $cell->nodeValue;
			$index += 1;
		}
		// account for initial terrain penalties
		$income = $coast;
		if($feature=='ice'){$income=$income-4;} 
		if($feature=='jungle'){$income=$income-2;} 
		if($feature=='wood'){$income=$income-1;} 
		if($feature=='taiga'){$income=$income-1;} 
		if($feature=='aridification'){$income=$income-1;}
		$adjacent = explode(' ',$adjacent);
		//print_r($adjacent);
		//echo "\n";
		array_push($adjacent,$id);
		//$noAdjacent = array('ocean','mountain','desert');
		//if(in_array($terrain,$noAdjacent)){$adjacent=array();$income=0;} // make these trers contribute nothing, so civs are not based around them
		//$noFeature = array('ice','jungle'); // can pass along taiga but not jungle
		//if(in_array($terrain,$noFeature)){$adjacent=array();$income=0;}
		
		// build up each ter
		$ter = array( // build up dictionary to be element of data2
		$id => $name, // this allows the ter to be referenced by its id for checking adjacent ters
		'adjacent' => $adjacent,
		'bids' => array(), // territory is initially empty
		'coast' => $coast,
		'color' => '#000000', // default is black
		'feature' => $feature,
		'id' => $id,
		'income' => $income,
		'name' => $name,
		'overflow' => 0, // civs start with 5 extra points from overflow, not ters
		'owner' => $name, // default owner is self
		'resource' => $resource, 
		'stability' => 0,
		'terrain' => $terrain,
		'wonders' => 0,
		);
		$ter = AllTech($ter); // turn this off when starting from ancient
		// assign territories to the overall board, identified by full names; must be no caps, spaces, or apostrophes
		$board['ters'][$name] = $ter; 
	}
	// build up a dictionary mapping ids to full names
	$id_to_name = array(); 
	foreach ($board['ters'] as $territory){
		$ID = $territory['id'];
		$NAME = $territory['name'];
		$id_to_name[$ID] = $NAME;
	}
	// use this dictionary to convert each adjacent from array of ids to array of names
	foreach ($board['ters'] as $territory){
		$ADJACENT = $territory['adjacent']; // list of ids
		$new_adjacent = array(); // to be list of names
		foreach ($ADJACENT as $neighbor){
			array_push($new_adjacent,$id_to_name[$neighbor]);
		}
		$territory['adjacent'] = $new_adjacent;
		$board['ters'][$territory['name']] = $territory;
	}
	return $board;
}

// not used when startiung from ancient //
function AllTech($Ter){
	$terrain = $Ter['terrain'];	
	$feature = $Ter['feature'];	
	$resource = $Ter['resource'];	
	$income = $Ter['income'];

	if($terrain=='grass'){$income=$income+1;}
	if($feature=='ice'){$income=$income+2;} // global warming
	if($feature=='wood'){$income=$income-1;} // aridification
	if($feature=='jungle'){$income=$income-1;} // aridification
	if($resource=='uranium'){$income=$income+3;}

	if($terrain=='plain'){$income=$income+1;}
	if($terrain=='hill'){$income=$income+1;}
	if($terrain=='river'){$income=$income+1;}
	if($terrain=='grass'){$income=$income+1;}
	if($terrain=='tundra'){$income=$income+1;}
	if($feature=='ice'){$income=$income+1;} // global warming
	if($feature=='jungle'){$income=$income+2;}
	if($feature=='taiga'){$income=$income+1;}
	if($resource=='copper'){$income=$income+1;}
	if($resource=='coal'){$income=$income+1;}
	if($resource=='sugar'){$income=$income+1;}
	if($resource=='oil'){$income=$income+2;}
	if($resource=='drugs'){$income=$income+1;}
	if($resource=='iron'){$income=$income+1;}
	//if($resource=='textiles'){$income=$income+1;} // nerfed because textiles OP
	if($resource=='tin'){$income=$income+1;}

	if($terrain=='grass'){$income=$income+1;}
	if($terrain=='river'){$income=$income+1;}
	if($resource=='fur'){$income=$income+1;}
	if($resource=='silk'){$income=$income+1;}
	if($resource=='spice'){$income=$income+1;}
	if($resource=='sugar'){$income=$income+1;}
	if($resource=='gems'){$income=$income+1;}
	if($resource=='iron'){$income=$income+1;}
	if($resource=='coal'){$income=$income+2;}

	if($terrain=='flood'){$income=$income+1;}
	if($resource=='incense'){$income=$income+1;}
	if($resource=='drugs'){$income=$income+1;}

	if($terrain=='hill'){$income=$income+1;}
	if($terrain=='river'){$income=$income+1;}
	if($terrain=='flood'){$income=$income+1;}
	if($feature=='wood'){$income=$income+2;}
	if($resource=='alcohol'){$income=$income+1;}
	if($resource=='textiles'){$income=$income+1;}
	if($resource=='iron'){$income=$income+1;}

	if($terrain=='plain'){$income=$income+1;}
	if($terrain=='flood'){$income=$income+1;}
	if($resource=='gems'){$income=$income+1;}
	if($resource=='fur'){$income=$income+1;}
	if($resource=='silk'){$income=$income+1;}
	if($resource=='oil'){$income=$income+1;}
	if($resource=='alcohol'){$income=$income+1;}
	if($resource=='tin'){$income=$income+1;}
	if($resource=='copper'){$income=$income+1;}
	if($resource=='textiles'){$income=$income+2;}
	if($resource=='gold'){$income=$income+2;}

	if($terrain=='grass'){$income=$income+1;} 
	if($terrain=='river'){$income=$income+1;} 
	if($terrain=='flood'){$income=$income+1;}

	$Ter['income'] = $income;
	return $Ter;
}

// 3 // add players to the game state
function AddCivs($board){
	$modernMap = $board['reference']['modern'];	
	$rand = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
	foreach($board['ters'] as $ter){
		$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
		$board['ters'][$ter['name']]['owner'] = 'none';
		$board['ters'][$ter['name']]['player'] = 'none';}
	foreach($modernMap as $name => $territory){
		$color = '#'.$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)].$rand[rand(0,15)];
    	$civ = array(
		'color' => $color, // default black, gain a color when score gets high enough
		'colorlock' => false, // prevents color from being changed once earned
		'income' => 0, // will be defined as the sum of incomes of ters
		'name' => $name,
		'capital' =>$territory[0],
		'overflow' => 0, // initial default points for civs, 4 of which are typically spent on neolithic techs
		'player' => 'weighted', // default AI bids randomly from options
		'research' => array(), // this will be set to this anyway
		'score' => 0, // will be built up by ters (wonders, income) and self (overflow, techs)
		'stability' => 0, //default stability is 0, helps define stability of each ter 
		'technology' => $board['reference']['tech'], // list of names of technologies owned
		'ters' => $territory,
		'trade' => array(), // list of deals with other civilizations; initial luxury boost goes to income of ter
		'wealth' => 0 // initially sum of income and overflow and decreases as points are spent on a round
		);
		//if($name=='united_states'){$civ['player']='greedy';}
		foreach($civ['ters'] as $tername){
			$board['ters'][$tername]['owner'] = $name;
		}
		$adjacent = array();
		foreach($territory as $ter){
			foreach($board['ters'][$ter]['adjacent'] as $neighbor){
				array_push($adjacent,$neighbor);
			}
		}
		//foreach($board['ters'] as $ter){
			//echo '~~~ ';
			//print_r($ter['name']);
			//foreach($ter['adjacent'] as $neighbor){
				//array_push($adjacent,$neighbor);}}
		$civ['adjacent'] = $adjacent;
		$board['civs'][$civ['name']] = $civ;
	}
	$board = UpdateGame($board); // call once to get starting map
	return $board;
}

// 4 // determine if there is a winner
function PlayGame($board){
	if($board['highscore']>=$board['max']){
		echo '>>>>> ',$board['winner'],' wins by income in round '.$board['round'].' with ',$board['highscore'],' points';
		DisplayMaps($board);
		return $board;
	}
	$board = EvalIncome($board); // sum overflow of civ and income from each ter; can be caused by tech diffusion
	//if($board['round']==1){foreach($board['civs'] as $civ){if($civ['income']>0){echo ' ~~~',$civ['name'],$civ['income'];}}}
	//foreach($board['civs'] as $civ){if($civ['income']>50){echo ' Big Civ ',$civ['name'],$civ['income'];}}
	$board = ListOptions($board); // edit data structure to update what can be done by each civ
	$board = GetOrders($board); // determine actions by AI and form input
	$board = UpdateGame($board); // evaluate each ter and color/save the svg; also update total scores and round
	echo ' >>>>> Round '. $board['round'] . ', '. $board['winner'] . ' winning at ' . $board['highscore'] . ' points. ';
	//echo $board['round'],$board['winner'],$board['highscore'],'~~~ ';
	//echo '<br> <img src="' . $board['title'] . $board['round']-1. '.svg' . '"width="780" height="520"> <br>';
	return PlayGame($board);
}

// 5 // break away ters with negative stability
function RebelTers($board){
	foreach($board['ters'] as $ter){
		if($ter['stability']<0){
			$oldOwner = $ter['owner'];
			$ter['owner'] = $ter['name'];
			array_push($board['civs'][$ter['name']]['ters'],$ter['name']);
			$board['civs'][$ter['name']]['technology'] = $board['civs'][$oldOwner]['technology'];
			// note that this may make a ter rebel to an existing civ, particularly as that civ lost its capital
			// other stuff like score and color will be evaluated later anyway
			// will implement mechanics to make multiple ters break way to single new civ later
			// espionage is simply investing points to grab these ters as they rebel, implemented later 
		}
	}
	foreach($board['civs'] as $civ){
		$index = 0;
		foreach($civ['ters'] as $ter){
			if($ter['owner']!=$civ['name']){
				unset($civ['ters'][$index]);
			}
			$index ++;
		}
	}
	return $board;
}

// 6 // evaluate income for each player to spend
function EvalIncome($board){
	$colorOptions = $board['reference']['upgrade'];
	$colorCheck = $board['reference']['check'];
	foreach($board['civs'] as $civ){// clear previous posessions to be redefined by owners of each ter
		$board['civs'][$civ['name']]['ters'] = array();
		$board['civs'][$civ['name']]['income'] = 0;
	}
	foreach($board['ters'] as $ter){ // build up income by ter
		if($ter['income']<0){$points=0;} // temporarily raise all income to 1
		else{$points = $ter['income'];}
		//echo $ter['name'],$points,$ter['owner'],'>> ';
		$owner = $ter['owner'];
		if($owner!='none'){
			$board['civs'][$owner]['income'] = $board['civs'][$owner]['income'] + $points;
			//echo $ter['name'],$points,$owner,$board['civs'][$owner]['income'],'*** ';
			array_push($board['civs'][$owner]['ters'],$ter['name']);
		}
	}
	foreach($board['civs'] as $civ){
		$civname = $civ['name'];
		//if($civname=='united_states'){echo ' USA';echo $civ['wealth'];echo ' ';echo $civ['overflow'];echo ' ';echo $civ['income'];}
		//echo $civname;echo $civ['income'];echo ' ^ ';
		$civ['wealth'] = $civ['overflow'] + $civ['income'];
		$civ['overflow'] = 0;
		$civ['score'] = $civ['score'] + $civ['income'];
		$board['civs'][$civname] = $civ;
		//if($civ['income']>0){echo $civ['name'],$civ['income'],' ';}
	}
	return $board;
}

// 7 // update the options for each player
function ListOptions($board){
	foreach($board['civs'] as $civ){		
		// determine avaialable ters
		$civname = $civ['capital'];
		$options = array($civname); // include starting ter
		$possessions = $civ['ters'];
		foreach($possessions as $tername){ // iterate through owned ters
			$ads = $board['ters'][$tername]['adjacent'];
			foreach($ads as $name){ // iterate through adjacents of each ter
				if(!in_array($name,$options)){
					array_push($options,$name);
				}		
			}
		}
		$civ['adjacent'] = $options;
		// determine available techs
		$techList = $civ['technology'];
		$techOptions = array();
		foreach($techList as $tech => $needed){
			$open = true;
			foreach($needed as $prerequisite){
				if($prerequisite){
					$open = false;
				}
			}
			if($open){array_push($techOptions,$tech);}
		}
		$civ['research'] = $techOptions;
		//$board['civs'][$civname] = $civ;
		if($techOptions == array()){
			$board['winner'] = $civname;
			$board['techDone'] = true;
		}
	}
	return $board;
}

// 8 // apply the AI and evaluate form inputs to edit board
function GetOrders($board){
	// first we need to clear all old orders from the board
	foreach($board['ters'] as $ter){
		foreach($board['civs'] as $bidder){
			$board['ters'][$ter['name']]['bids'][$bidder['name']] = 0;
		}
	}
	foreach($board['civs'] as $civ){
		$wealth = $civ['wealth'];
		$adjacent = $civ['adjacent'];
		$research = $civ['research'];
		if($civ['player']=='default'){
			$rand_bids = array_rand($adjacent,$wealth);
			foreach($rand_bids as $bid){
				$target = $adjacent[$bid];
				$board['ters'][$target]['bids'][$civ['name']]++; // civ will be referenced by name
				$board['civs'][$civ['name']]['wealth']--;
			}
		}
		if($civ['player']=='techy'){
			// now determine how much wealth to spend on tech
			$techWealth = $wealth;
			// spend wealth on tech purchases, randomly selected, without ability to beeline
			$techList = $board['reference']['tech'];
			$techCost = $board['reference']['techCost'];
			while($techWealth>0){
				$researchOptions = array(); // reset by the following loop anyway, defined directly by $research
				foreach($techCost as $technology=>$cost){
					if(in_array($technology,$research)){
						if($cost<=$techWealth){
							array_push($researchOptions,$technology);
						}
					}
				}
				$rand_tech = array_rand($researchOptions,1);
				$techChoice = $researchOptions[$rand_tech];
				$index = 0;
				foreach($research as $option){
					if($option==$techChoice){
						$research[$index] = 'void'; // placeholder brute force to prevent trying tech again
					}
					$index++;
				}
				if($techWealth>0){$board = BuyTech($board,$civ['name'],$techChoice,$techWealth);} // each civ is updated here
				$techWealth = $techWealth - $board['reference']['techCost'][$techChoice]; // pay for tech
				$board['civs'][$civ['name']]['score'] = $board['civs'][$civ['name']]['score']+$board['reference']['techCost'][$techChoice]; // increase score
				if($researchOptions==array()){$board['civs'][$civ['name']]['overflow']=$techWealth;$techWealth=0;} // send remaining wealth to overflow
			}	
		}
		if($civ['player']=='weighted'){
			$weighted = array();
			foreach($adjacent as $prey){ // build array that values ters
				$w = $board['ters'][$prey]['income'] + 1; // ters with 0 never get bidded on unless ther is +1
				//if($board['ters'][$prey]['owner']==$civ['name']){$w = 1;} // weight all owned ters to 1, all offense
				for($i=0;$i<=$w;$i++){// +1 to account for income=0
					array_push($weighted,$prey);
				}
			}
			// now determine how much wealth to spend on tech
			$techWealth = rand(0,ceil($wealth)); // bid less on tech than ter
			$wealth = $wealth - $techWealth;
			// spend wealth on tech purchases, randomly selected, without ability to beeline
			$techList = $board['reference']['tech'];
			$techCost = $board['reference']['techCost'];
			while($techWealth>0){
				$researchOptions = array();
				foreach($research as $technology){
					$cost = $techCost[$technology];
				}
				foreach($techCost as $technology=>$cost){
					if(in_array($technology,$research)){
						if($cost<=$techWealth){
							array_push($researchOptions,$technology);
						}
					}
				}
				//if($researchOptions==array()){$wealth=$wealth+$techWealth;$techWealth=0;} // send remaining wealth to bid on ters
				if($researchOptions==array()){$board['civs'][$civ['name']]['overflow']=$techWealth;$techWealth=0;} // send remaining wealth to overflow
				else{
				$rand_tech = array_rand($researchOptions,1);
				$techChoice = $researchOptions[$rand_tech];
				$index = 0;
				foreach($research as $option){
					if($option==$techChoice){
						$research[$index] = 'void'; // placeholder brute force to prevent trying tech again
					}
					$index++;
				}
				if($techWealth>0){$board = BuyTech($board,$civ['name'],$techChoice,$techWealth);} // each civ is updated here
				$techWealth = $techWealth - $board['reference']['techCost'][$techChoice]; // pay for tech
				$board['civs'][$civ['name']]['score'] = $board['civs'][$civ['name']]['score']+$board['reference']['techCost'][$techChoice]; // increase score
				}
			}
			// spend wealth on ter bids
			while ($wealth > 1){
				//echo ' #'; echo $wealth;echo ' ';echo sizeof($weighted);echo ' $ ';
				$rand_bid = array_rand($weighted,1);
				$target = $weighted[$rand_bid];
				$board['ters'][$target]['bids'][$civ['name']]++; // civ will be referenced by name
				$board['civs'][$civ['name']]['wealth']--;
				$wealth--;
			}
		}
		if($civ['player']=='greedy'){
			$weighted = array();
			foreach($adjacent as $prey){ // build array that values ters
				$w = $board['ters'][$prey]['income'] + 1; // ters with 0 never get bidded on unless ther is +1
				if($board['ters'][$prey]['owner']==$civ['name']){$w = 1;} // weight all owned ters to 1, all offense
				for($i=0;$i<=$w;$i++){// +1 to account for income=0
					array_push($weighted,$prey);
				}
			}
			// now determine how much wealth to spend on tech
			$techWealth = rand(0,ceil($wealth)); // bid less on tech than ter
			$wealth = $wealth - $techWealth;
			// spend wealth on tech purchases, randomly selected, without ability to beeline
			$techList = $board['reference']['tech'];
			$techCost = $board['reference']['techCost'];
			while($techWealth>0){
				$researchOptions = array();
				foreach($research as $technology){
					$cost = $techCost[$technology];
				}
				foreach($techCost as $technology=>$cost){
					
					if(in_array($technology,$research)){
						if($cost<=$techWealth){
							array_push($researchOptions,$technology);
						}
					}
				}
				$rand_tech = array_rand($researchOptions,1);
				$techChoice = $researchOptions[$rand_tech];
				$index = 0;
				foreach($research as $option){
					if($option==$techChoice){
						$research[$index] = 'void'; // placeholder brute force to prevent trying tech again
					}
					$index++;
				}
				if($techWealth>0){$board = BuyTech($board,$civ['name'],$techChoice,$techWealth);} // each civ is updated here
				$techWealth = $techWealth - $board['reference']['techCost'][$techChoice]; // pay for tech
				$board['civs'][$civ['name']]['score'] = $board['civs'][$civ['name']]['score']+$board['reference']['techCost'][$techChoice]; // increase score
				//if($researchOptions==array()){$wealth=$wealth+$techWealth;$techWealth=0;} // send remaining wealth to bid on ters
				if($researchOptions==array()){$board['civs'][$civ['name']]['overflow']=$techWealth;$techWealth=0;} // send remaining wealth to overflow
			}
			// spend wealth on ter bids
			$rand_bids = array_rand($weighted,$wealth);
			foreach($rand_bids as $bid){
				$target = $weighted[$bid];
				$board['ters'][$target]['bids'][$civ['name']]++; // civ will be referenced by name
				$board['civs'][$civ['name']]['wealth']--;
			}
		}
	}
	return $board;
}

// 9 // add tech to civ, updating the ters owned but benefits kick in next round 
function BuyTech($board,$civname,$tech,$wealth){
	// A // update civ's tech list
	//echo '+++',$tech,$board['ters'][$civname]['income'],'+++ ';
	$techTree = $board['civs'][$civname]['technology'];
	$techTree[$tech] = array('have'); // prevents tech from being added to queue in future, as that only looks for ones that map to 'none'
	// remove tech as prerequisite from all techs that require it
	foreach($techTree as $techname=>$needed){
		foreach($needed as $count => $pre){
			if($pre==$tech){
				//echo '$$$$$',$tech,$pre;
				unset($techTree[$techname][$count]);
			}
		}
	}
	$board['civs'][$civname]['technology'] = $techTree;
	// B // update income of each ter in civ
	foreach($board['ters'] as $ter){
		//print_r($ter['name']);
		//echo ' ~ ';
		if($ter['owner']==$civname){
			$ter=TerTech($ter,$tech);
			//if($ter['name']=='baghdad'){echo $ter['owner'],$ter['name'],$ter['income'],'~~~';}
			$board['ters'][$ter['name']]=$ter;
			//if($civname=='baghdad'){echo $ter['income'],'$$ ';}
			//echo $board['ters']['baghdad']['income'],'&A';
			//if($civname=='baghdad'){echo 'AAA',$board['ters'][$ter['name']]['income'],'AAA';}
		}
	}
	//echo $board['ters']['baghdad']['income'],'&B';
	return $board;
}

// 10 // update the income of a ter when it gets new tech
function TerTech($Ter,$tech) // returns ter with the tech added
{
	//echo $Ter['name'],$tech,'~~~~~~ ';
	$terrain = $Ter['terrain'];	
	$feature = $Ter['feature'];	
	$resource = $Ter['resource'];	
	// $era = $Ter['era']; // no longer used but may be later
	$income = $Ter['income'];
	//if($Ter['name']=='baghdad'){echo '~~~',$income,$tech;}
	
	if($tech=='writing'){;}
	if($tech=='agriculture'){
		//if($Ter['name']=='baghdad'){echo '>>>',$income;}
		if($terrain=='grass'){$income=$income+1;} 
		if($terrain=='river'){$income=$income+1;} 
		if($terrain=='flood'){$income=$income+1;};}
	if($tech=='wheel'){;}
	if($tech=='smelting'){;}
	
	if($tech=='arithmetic'){if($resource=='gems'){$income=$income+1;}if($resource=='gold'){$income=$income+2;};}
	if($tech=='husbandry'){
		if($terrain=='plain'){$income=$income+1;}
		if($terrain=='flood'){$income=$income+1;}
		if($resource=='textiles'){$income=$income+2;}
		if($resource=='fur'){$income=$income+1;}
		if($resource=='silk'){$income=$income+1;};}
	if($tech=='ceramics'){if($resource=='oil'){$income=$income+1;}if($resource=='alcohol'){$income=$income+1;};}
	if($tech=='alloying'){if($resource=='tin'){$income=$income+1;}if($resource=='copper'){$income=$income+1;};}
	
	if($tech=='geometry'){if($resource=='alcohol'){$income=$income+1;}if($resource=='textiles'){$income=$income+1;};}
	if($tech=='plumbing'){if($terrain=='hill'){$income=$income+1;}
		if($terrain=='river'){$income=$income+1;}
		if($terrain=='flood'){$income=$income+1;};}
	if($tech=='forestry'){if($feature=='wood'){$income=$income+2;};}
	if($tech=='furnace'){if($resource=='iron'){$income=$income+1;};}
	
	if($tech=='philosophy'){if($resource=='incense'){$income=$income+1;}if($resource=='drugs'){$income=$income+1;};}
	if($tech=='alchemy'){if($resource=='alcohol'){$income=$income+1;};}
	if($tech=='concrete'){if($terrain=='flood'){$income=$income+1;};}
	if($tech=='casting'){;}
	
	if($tech=='paper'){;}
	if($tech=='fertilizer'){;}
	if($tech=='ships'){;}
	if($tech=='firearm'){;}
	
	if($tech=='science'){if($resource=='gems'){$income=$income+1;};}
	if($tech=='rotation'){
		if($terrain=='grass'){$income=$income+1;}		
		if($resource=='fur'){$income=$income+1;}
		if($resource=='silk'){$income=$income+1;}
		if($resource=='spice'){$income=$income+1;}
		if($resource=='sugar'){$income=$income+1;};}
	if($tech=='machinery'){if($terrain=='river'){$income=$income+1;}if($resource=='coal'){$income=$income+1;};}
	if($tech=='steel'){if($resource=='iron'){$income=$income+1;}if($resource=='coal'){$income=$income+1;};}
	
	if($tech=='electricity'){if($resource=='copper'){$income=$income+1;}if($resource=='coal'){$income=$income+1;};}
	if($tech=='chemistry'){
		if($feature=='jungle'){$income=$income+2;}		
		if($resource=='sugar'){$income=$income+1;}
		if($resource=='oil'){$income=$income+1;}
		if($resource=='drugs'){$income=$income+1;};}
	if($tech=='engine'){
		if($resource=='iron'){$income=$income+1;}
		if($resource=='oil'){$income=$income+1;}
		if($terrain=='river'){$income=$income+1;}
		if($feature=='taiga'){$income=$income+1;};}
	if($tech=='factory'){
		if($terrain=='plain'){$income=$income+1;}
		if($terrain=='hill'){$income=$income+1;}
		if($terrain=='grass'){$income=$income+1;}
		if($terrain=='tundra'){$income=$income+1;}
		if($feature=='ice'){$income=$income+2;} // global warming
		if($feature=='jungle'){$income=$income-1;} // aridification
		if($feature=='wood'){$income=$income-1;} // aridification
		if($resource=='tin'){$income=$income+1;};}
	
	if($tech=='computing'){;}
	if($tech=='genetics'){if($terrain=='grass'){$income=$income+1;};}
	if($tech=='flight'){;}
	if($tech=='nuclear'){if($resource=='uranium'){$income=$income+3;};}
	
	if($tech=='quantum'){;}
	if($tech=='potency'){;}
	if($tech=='nanotech'){;}
	if($tech=='laser'){;}
	//if($Ter['name']=='baghdad'){echo $income,'--- ';}
	$Ter['income'] = $income;
	$Ter['technology'][$tech] = true;
	return $Ter;
}

// 11 // update the game state by each territory
function UpdateGame($board){ // takes in an svg from the server ImageSvgFile and outputs a new one myFile colored by ColorMap, or ImageColor for all ters that are not specified
    $FileContents = file_get_contents('world_2016.svg');
    $doc = new DOMDocument();
	$dom = new StdClass;
    $dom->preserveWhiteSpace = False;
    $doc->loadXML($FileContents) or die('Failed to load SVG file ' . 'optimized_svg.svg' . ' as XML.  It probably contains malformed data.');
    $AllTags = $doc->getElementsByTagName("path");
    $map = $board['ters'];
    foreach ($AllTags as $ATag) // iterate through all territories in the svg and color them appropriately
    {
    	$VectorColor = $ATag->getAttribute('fill'); // this is not used, but may be later
		$tername = $ATag->getAttribute('id'); // get the name of each ter; this is different fron the 3-character ID that Excel uses to reference adjacent ones
		$ter = $map[$tername];
		$bids = $ter['bids'];
		$highest = 0;
		$winner = $ter['owner'];
		foreach($bids as $bidder => $bid){
			if($bidder!=$winner){
				if($bid<=$highest){$winner = $ter['owner'];} // in case of equal bids (bounce)
				if($bid>$highest){
					//echo $winner;echo ' loses ';echo $tername;echo ' to ';echo $bidder;echo ' # ';
					$winner = $bidder;$highest=$bid;
					$map[$tername]['owner']=$winner;
				}
			}
		}
		//if($ter['income']>5){$ter['stability'] = -1;echo $ter['name'],$board['round'];}
		//if($ter['stability']<0){echo 'potato';$winner=$ter['name'];}
		//foreach($ter['technology'] as $techname){
			//$board['civs'][$winner]['technology'][$techname] = array('have');
		//}
		if($winner=='none'){$color='#000000';}
		else{$color = $board['civs'][$winner]['color'];}
		//$ter['owner'] = $winner;
		//$map[$tername] = $ter;
		//if($ter['resource']=='-'){$color='#FFFFFF';} 
		$noAdjacent = array('ocean','sea','mountain');//list of terrains that are not colored
		if(in_array($ter['terrain'],$noAdjacent)){$color='#000000';} 
		//if($ter['resource']=='alcohol'){$color='#9900CC';} 
		//if($ter['resource']=='coal'){$color='#99CC00';} 
		//if($ter['resource']=='copper'){$color='#FF9900';} 
		//if($ter['resource']=='drugs'){$color='#009933';} 
		//if($ter['resource']=='fur'){$color='#800000';} 
		//if($ter['resource']=='gems'){$color='#FFFF00';} 
		//if($ter['resource']=='gold'){$color='#FFCC66';} 
		//if($ter['resource']=='incense'){$color='#3333FF';} 
		//if($ter['resource']=='iron'){$color='#999966';} 
		//if($ter['resource']=='oil'){$color='#CC9900';} 
		//if($ter['resource']=='silk'){$color='#996633';} 
		//if($ter['resource']=='spice'){$color='#009999';} 
		//if($ter['resource']=='sugar'){$color='#FF0000';} 
		//if($ter['resource']=='textiles'){$color='#FF99FF';} 
		//if($ter['resource']=='tin'){$color='#33CCFF';} 
		//if($ter['resource']=='uranium'){$color='#66FF66';} 
		//if($board['civs'][$ter['owner']]['income']<1){$color='#000000';} 
		$ATag->setAttribute('fill',$color);
	}
	$board['ters'] = $map;
	$board['highscore'] = 0; //reset so that it can go down
	foreach($board['civs'] as $civ){
		$total = $civ['income'];
		//if($civ['name']=='united_states'){echo$civ['income'];echo' # ';}
		//$total = $civ['score'];
		if($total>$board['highscore']){$board['highscore']=$total;$board['winner']=$civ['name'];}
		$FileContents = $doc->saveXML($doc);
    } 
	$board['round']++;
	$NewFile = ('boards/' . $board['title'] . $board['round'] . '.svg');
	$fh = fopen($NewFile, 'w'); // open new file
	fwrite($fh, $FileContents); // write new file
	fclose($fh);
	return $board;
}         

// 12 // outyput a map for each round at the end
function DisplayMaps($game){
	$final = $game['round'];
	$name = $game['title'];
	$string = '';
	for($step=1;$step<=$final;$step++){
		$fileName = $name . $step . '.svg';
		//if($final==$step){$string = '<img src="' . $fileName . '"width="760" height="520"> <br>';}
		$string = $string . '<p>' . $fileName . '</p>' . '<img src="' . 'boards/' . $fileName . '"width="780" height="520"> <br>';
	}
	return $string;
}

set_time_limit(100); // typically for 5 minutes, or 300 seconds
ini_set('memory_limit', -1); // this is really sketchy
echo Simulation('More_Testing_',160);
?> 
