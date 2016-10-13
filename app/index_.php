<?
include 'utils/api.php';

$clanid = 0;
$clanTag = '';
$clanName = '';
$clanColor = '';
$clanMotto = '';
$clanLogo = '';
if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'id'"))) $clanid = $f['value'];
if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'tag'"))) $clanTag = $f['value'];
if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'name'"))) $clanName = $f['value'];
if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'color'"))) $clanColor = $f['value'];
if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'motto'"))) $clanMotto = $f['value'];
if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT `value` FROM `data` WHERE `variable` = 'logo'"))) $clanLogo = $f['value'];

toLog('<br />LOG START index_.php ' . date('Y-m-d H:i:s') . '<br />');
$member = null;
$member_name = null;
$rights = 'guest';
if (isset($_COOKIE['sessionID'])) {
	toLog('COOKIE TRUE<br />');
	if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `members` WHERE `sessionID` = '" . $_COOKIE['sessionID'] . "'"))) {
		toLog('MEMBER TRUE<br />');
		$member = $f['id'];
		$rights = $f['rights'];
		$member_name = $f['name'];
		toLog('member = ' . $member . '<br />');
		toLog('rights = ' . $rights . '<br />');
		toLog('member_name = ' . $member_name . '<br />');
		setcookie('sessionID', $f['sessionID'], time() + 1209600);
		session_id($_COOKIE['sessionID']);
	} else {
		setcookie('sessionID', $f['sessionID'], time() - 1209600);
	};
};
session_start();
toLog('session_id = ' . session_id() . '<br />');
toLog('session = ' . json_encode($_SESSION) . '<br />');
if ($member != null) $_SESSION['login'] = $member;
toLog('session = ' . json_encode($_SESSION) . '<br />');
if (isset($_SESSION['login']) && $_SESSION['login'] != '') {
	if ($f = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM `members` WHERE `id` = '" . $_SESSION['login'] . "'"))) {
		toLog('SESSION login TRUE<br />');
		$member = $_SESSION['login'];
		$member_name = $f['name'];
		$rights = $f['rights'];
		toLog('member = ' . $member . '<br />');
		toLog('rights = ' . $rights . '<br />');
		toLog('member_name = ' . $member_name . '<br />');
	};
};
if (isset($_SESSION['nickname']) && $member_name == null) $member_name = $_SESSION['nickname'];

$date = date('Y-m-d', time());
$time = date('H:i:s', time());
$mem_str = $member;
if ($mem_str == null) $mem_str = 'NULL';
$ip = $_SERVER['REMOTE_ADDR'];
$cookie = session_id();
$browser = $_SERVER['HTTP_USER_AGENT'];
$visits = array();
$update = false;
if ($rs = mysqli_query($link, "SELECT * FROM `visitors` WHERE `date` = '$date' AND `member` = '$mem_str' AND `ip` = '$ip' AND `cookie` = '$cookie' AND `browser` = '$browser'")) {
	if ($f = mysqli_fetch_assoc($rs)) {
		$visits = objectToArray(json_decode($f['visits']));
		$update = true;
	};
};
$visits[] = $time;
$str_visits = json_encode($visits);
if ($update) $query = "UPDATE `visitors` SET `visits` = '$str_visits' WHERE `date` = '$date' AND `member` = '$mem_str' AND `ip` = '$ip' AND `cookie` = '$cookie' AND `browser` = '$browser'";
else $query = "INSERT INTO `visitors` (`date`, `ip`, `cookie`, `member`, `visits`, `browser`) VALUES ('$date', '$ip', '$cookie', $mem_str, '$str_visits', '$browser')";
mysqli_query($link, $query);
mysqli_close($link);
?>
	<!DOCTYPE html>
	<html lang="ru">
	<head>
		<meta charset="utf-8"/>
		<meta name="Keywords" content="World of Tanks, worldoftanks, WoT, вот, танки, клан, миротворцы россии, MIR-R">
		<title>[MIR-R] Миротворцы России</title>
		<link rel="icon" href="favicon.ico">
		<link rel="stylesheet" type="text/css" href="styles/jquery-ui-1.9.1.custom.min.css"/>
		<link rel="stylesheet/less" type="text/css" href="styles/mainStyle.less"/>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
		<script src="scripts/less.min.js"></script>
		<script src="scripts/mainScript.js"></script>
		<? if ($rights == 'admin' || $rights == 'sadmin') { ?>
			<script src="scripts/detect.js"></script>
		<? }; ?>
	</head>
	<body class="h-container">
	<header class="h-auto">
		<div id="logo">
			<a href="http://worldoftanks.ru/community/clans/19445-MIR-R/#wot&mt_order_by=-member_since" target="_blank">
				<div><span style="color:<? echo $clanColor; ?>;">[<? echo $clanTag; ?>]</span> <? echo $clanName; ?>
				</div>
				<div id="motto"><? echo $clanMotto; ?></div>
			</a>
		</div>
		<div id="enter">
			<div id="menuLogin" menu="login" class="button"><? echo($member == null ? 'Вход' : 'Выход'); ?></div>
			<div id="member" rights="<? echo $rights; ?>" member="<? echo $member; ?>"><? echo $member_name; ?></div>
		</div>
		<div id="menu" action="buttonAll" type="buttonCheck">
			<div id="mainCommands" action="buttonMain" type="buttonRadio">
				<div id="menuMain" menu="main" class="button active">Главная</div>
				<? if ($rights == 'admin' || $rights == 'sadmin') { ?>
					<div id="menuAdmin" menu="admin" class="button">Админка</div>
				<? }; ?>
				<div id="menuGraph" menu="graph" class="button">Графики</div>
				<div id="menuStat" menu="stat" class="button">Статистика</div>
				<? if ($rights == 'member' || $rights == 'admin' || $rights == 'sadmin') { ?>
					<div id="menuEvent" menu="event" class="button">События</div>
				<? }; ?>
			</div>
			<? if ($rights == 'admin' || $rights == 'sadmin') { ?>
				<div id="menuAll" class="button">Все</div>
			<? }; ?>
		</div>
	</header>
	<div id="main" class="h-full">
		<div id="mainContainer" curr_sheet="0">
			<div id="mainSheet" class="sheet active" menu="main" order="1">
				<div id="mainTab" class="sheet-inner">
					<div class="h-container">
						<div id="countMembers" class="h-auto"><span></span></div>
						<div id="tabMain" class="h-full">
							<div class="table"></div>
						</div>
					</div>
				</div>
			</div>
			<div id="extendSheet" class="sheet" menu="extend" view="all" order="2">
				<div class="sheet-inner h-container">
					<div class="h-auto">
						<div id="extendMenu" action="buttonExtend" type="buttonRadio">
							<div id="nameGameData"><span><span class="name"></span><span class="game"></span></span>
							</div>
							<div id="buttonAllTime" menu="all" class="button active">Общее</div>
							<div id="buttonPeriod" menu="period" class="button">Период</div>
							<div id="dateUpdated"><span>Последняя игра: <span class="date"></span></span></div>
						</div>
						<div id="extendPeriod" class="changeDate" type="period" start="2012-08-05"
							 action="buttonExtend()">
							<button class="rewind" step="-1" period="day">Предыдущий</button>
							<input type="text" name="date1" value="" class="inputDate" title=""/>
							<b>&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;</b>
							<input type="text" name="date2" value="" class="inputDate" title=""/>
							<button class="forward" step="1" period="day">Следующий</button>
						</div>
					</div>
					<div id="extendData" class="h-full">
						<div id="Result" class="borderBlock info">
							<div class="headerBlock"><span>Общее</span><span
									class="ui-icon ui-icon-circle-triangle-s"></span><span
									class="ui-icon ui-icon-circle-triangle-n"></span></div>
							<div class="infoBlock">
								<span>
									<div class="infoRow all" param="battles"><span>Проведено боёв</span><span>-</span>
									</div>
									<div class="infoRow all" param="wins"><span>Побед</span><span>-</span></div>
									<div class="infoRow all" param="losses"><span>Поражений</span><span>-</span></div>
									<div class="infoRow all" param="survived"><span>Выжил</span><span>-</span></div>
									<div class="infoRow all" param="xp"><span>Суммарный опыт</span><span>-</span></div>
									<div class="infoRow wot wotb" param="damageD">
										<span>Нанесено урона</span><span>-</span></div>
									<div class="infoRow wowp wows" param="damage">
										<span>Нанесено урона</span><span>-</span></div>
									<div class="infoRow wot wotb" param="damageR">
										<span>Получено урона</span><span>-</span></div>
									<div class="infoRow all" param="frags"><span>Уничтожено</span><span>-</span></div>
									<div class="infoRow wot wotb" param="spotted"><span>Обнаружено</span><span>-</span>
									</div>
									<div class="infoRow wot wotb wows" param="capture">
										<span>Захват базы</span><span>-</span></div>
									<div class="infoRow wot wotb wows" param="dropped">
										<span>Защита базы</span><span>-</span></div>
									<div class="infoRow wot wotb wowp" param="shots">
										<span>Выстрелов</span><span>-</span></div>
									<div class="infoRow wot wotb wowp" param="hits"><span>Попаданий</span><span>-</span>
									</div>
									<div class="infoRow wowp" param="objectsD">
										<span>Разрушено объектов</span><span>-</span></div>
									<div class="infoRow wowp" param="structureD">
										<span>Нанесено урона объектам</span><span>-</span></div>
									<div class="infoRow wowp" param="basesD"><span>Разрушено баз</span><span>-</span>
									</div>
									<div class="infoRow wowp" param="turretsD">
										<span>Разрушено орудий ПВО</span><span>-</span></div>
									<div class="infoRow wowp" param="assists">
										<span>Разрушено объектов</span><span>-</span></div>
								</span>
							</div>
						</div>
						<div id="Effect" class="borderBlock info">
							<div class="headerBlock"><span>Эффективность</span><span
									class="ui-icon ui-icon-circle-triangle-s"></span><span
									class="ui-icon ui-icon-circle-triangle-n"></span></div>
							<div class="infoBlock">
								<span>
									<div class="infoRow all" param="wins"><span>Процент побед</span><span>-</span></div>
									<div class="infoRow all" param="losses"><span>Процент поражений</span><span>-</span>
									</div>
									<div class="infoRow all" param="survived">
										<span>Процент выживания</span><span>-</span></div>
									<div class="infoRow all" param="xp"><span>Опыт за бой</span><span>-</span></div>
									<div class="infoRow all" param="damage">
										<span>Повреждений за бой</span><span>-</span></div>
									<div class="infoRow all" param="frags"><span>Фрагов за бой</span><span>-</span>
									</div>
									<div class="infoRow wot wotb" param="spotted">
										<span>Обнаружено за бой</span><span>-</span></div>
									<div class="infoRow wot wotb wows" param="capture">
										<span>Средний захват базы</span><span>-</span></div>
									<div class="infoRow wot wotb wows" param="dropped">
										<span>Средняя защита базы</span><span>-</span></div>
									<div class="infoRow wot wotb wowp" param="hits"><span>Процент попаданий</span><span>-</span>
									</div>
									<div class="infoRow all" param="technics"><span>Средний уровень техники</span><span>-</span>
									</div>
									<div class="infoRow wot" param="effBS"><span>Эффективность БС</span><span>-</span>
									</div>
									<div class="infoRow wot" param="effWN"><span>Эффективность WN</span><span>-</span>
									</div>
									<div class="infoRow wot" param="effWN6">
										<span>Эффективность WN-6</span><span>-</span></div>
									<div class="infoRow wot" param="effWN8">
										<span>Эффективность WN-8</span><span>-</span></div>
									<div class="infoRow all" param="battles"><span>Боёв в день</span><span>-</span>
									</div>
								</span>
							</div>
						</div>
						<div id="Difference" class="borderBlock info">
							<div class="headerBlock"><span>Изменения</span><span
									class="ui-icon ui-icon-circle-triangle-s"></span><span
									class="ui-icon ui-icon-circle-triangle-n"></span></div>
							<div class="infoBlock">
								<span>
									<div class="infoRow all" param="battles"><span>Боёв</span><span>-</span></div>
									<div class="infoRow all" param="wins"><span>Побед</span><span>-</span></div>
									<div class="infoRow all" param="losses"><span>Поражений</span><span>-</span></div>
									<div class="infoRow all" param="survived"><span>Выжил</span><span>-</span></div>
									<div class="infoRow all" param="xp"><span>Опыт</span><span>-</span></div>
									<div class="infoRow all" param="damage"><span>Повреждения</span><span>-</span></div>
									<div class="infoRow all" param="frags"><span>Уничтожено</span><span>-</span></div>
									<div class="infoRow wot wotb" param="spotted"><span>Обнаружено</span><span>-</span>
									</div>
									<div class="infoRow wot wotb wows" param="capture">
										<span>Захват базы</span><span>-</span></div>
									<div class="infoRow wot wotb wows" param="dropped">
										<span>Защита базы</span><span>-</span></div>
									<div class="infoRow wot wotb wowp" param="hits"><span>Процент попаданий</span><span>-</span>
									</div>
									<div class="infoRow all" param="technics"><span>Средний уровень техники</span><span>-</span>
									</div>
									<div class="infoRow wot" param="effBS"><span>Эффективность БС</span><span>-</span>
									</div>
									<div class="infoRow wot" param="effWN"><span>Эффективность WN</span><span>-</span>
									</div>
									<div class="infoRow wot" param="effWN6">
										<span>Эффективность WN-6</span><span>-</span></div>
									<div class="infoRow wot" param="effWN8">
										<span>Эффективность WN-8</span><span>-</span></div>
								</span>
							</div>
						</div>
						<div id="Medals" class="borderBlock hide">
							<div class="headerBlock"><span>Награды</span><span
									class="ui-icon ui-icon-circle-triangle-s"></span><span
									class="ui-icon ui-icon-circle-triangle-n"></span></div>
							<div class="infoBlock"><span></span></div>
						</div>
						<div id="Technics" class="borderBlock hide">
							<div class="headerBlock"><span>Техника</span><span
									class="ui-icon ui-icon-circle-triangle-s"></span><span
									class="ui-icon ui-icon-circle-triangle-n"></span></div>
							<div class="infoBlock"><span>
								<div id="tabTanks">
									<div class="table no-scroll"></div>
								</div>
							</span></div>
						</div>
					</div>
				</div>
			</div>
			<? if ($rights == 'admin' || $rights == 'sadmin') { ?>
				<div id="adminSheet" class="sheet" menu="admin" order="3">
					<div class="sheet-inner h-container">
						<div id="adminMenu" class="menu h-auto" action="buttonAdmin" type="buttonRadio">
							<div class="button" menu="visits">Посетители</div>
							<div class="button active" menu="events">События</div>
							<div class="button" menu="technics">Техника</div>
							<div class="button" menu="medals">Награды</div>
							<div class="button" menu="members">Игроки</div>
						</div>

						<div class="h-full">
							<div id="adminVisitors" class="adminBlock h-container" style="display:none;">
								<div id="adminVisitorsMenu" class="changeDate h-auto" type="day" start="month"
									 action="adminVisitorsCheckDate()">
									<button class="rewind" step="-1">Предыдущий</button>
									&nbsp;
									<input type="text" name="date" value="" class="inputDate" title=""/>
									&nbsp;
									<button class="forward" step="1">Следующий</button>
								</div>
								<div id="adminTableVisitors" class="adminTable h-full">
									<div class="table"></div>
								</div>
							</div>

							<div id="adminEvents" class="adminBlock h-container" style="display:none;">
								<div id="adminEventsMenu" class="changeDate h-auto" type="day" start="year"
									 action="adminEventsCheckDate()">
									<button class="rewind" step="-1">Предыдущий</button>
									&nbsp;
									<input type="text" name="date" value="" class="inputDate" title=""/>
									&nbsp;
									<button class="forward" step="1">Следующий</button>
								</div>
								<div id="adminEventsList" class="h-full"></div>
							</div>

							<div id="adminTechnics" class="adminBlock h-container" style="display:none;">
								<div class="h-auto">
									<div id="adminTechnicsMenu" class="menu" action="buttonAdminTechnics"
										 type="buttonRadio">
										<div class="button game active" menu="wot"><img src="images/logo/logo_wot.png"/>
										</div>
										<div class="button game" menu="wotb"><img src="images/logo/logo_wotb.png"/>
										</div>
										<div class="button game" menu="wowp"><img src="images/logo/logo_wowp.png"/>
										</div>
										<div class="button game" menu="wows"><img src="images/logo/logo_wows.png"/>
										</div>
									</div>
									<div id="adminTechnicsSelectors" class="selectors">
										<div selector="nations"></div>
										<br/>
										<div selector="types"></div>
										<div selector="levels"></div>
										<br/>
										<div selector="prem">
											<div class="select" prem="prem">
												<div class="text">Премиум</div>
											</div>
										</div>
										<div selector="stats">
											<div class="select" stat="new">
												<div class="text">Новые</div>
											</div>
											<div class="select" stat="chn">
												<div class="text">Измененные</div>
											</div>
											<div class="select" stat="del">
												<div class="text">Удаленные</div>
											</div>
											<div class="select" stat="clear">
												<div class="text">Сбросить фильтр</div>
											</div>
										</div>
										<div selector="confirm">
											<div class="confirm" confirm="new">
												<div class="text">Принять новые</div>
											</div>
											<div class="confirm" confirm="chn">
												<div class="text">Принять изменения: <span></span></div>
											</div>
										</div>
									</div>
								</div>
								<div id="adminTableTechnics" class="adminTable h-full">
									<div class="table"></div>
								</div>
							</div>

							<div id="adminMedals" class="adminBlock h-container" style="display:none;">
								<div class="h-auto">
									<div id="adminMedalsMenu" class="menu" action="buttonAdminMedals"
										 type="buttonRadio">
										<div class="button game active" menu="wot"><img src="images/logo/logo_wot.png"/>
										</div>
										<div class="button game" menu="wotb"><img src="images/logo/logo_wotb.png"/>
										</div>
										<div class="button game" menu="wowp"><img src="images/logo/logo_wowp.png"/>
										</div>
										<div class="button game" menu="wows"><img src="images/logo/logo_wows.png"/>
										</div>
									</div>
									<div id="adminMedalsSelectors" class="adminMedals selectors">
										<div selector="section"></div>
										<br/>
										<div selector="type"></div>
										<br/>
										<div selector="stats">
											<div class="select" stat="new">
												<div class="text">Новые</div>
											</div>
											<div class="select" stat="chn">
												<div class="text">Измененные</div>
											</div>
											<div class="select" stat="del">
												<div class="text">Удаленные</div>
											</div>
											<div class="select" stat="clear">
												<div class="text">Сбросить фильтр</div>
											</div>
										</div>
										<div selector="confirm">
											<div class="confirm" confirm="new">
												<div class="text">Принять новые</div>
											</div>
											<div class="confirm" confirm="chn">
												<div class="text">Принять изменения: <span></span></div>
											</div>
										</div>
									</div>
								</div>
								<div id="adminTableMedals" class="adminTable h-full">
									<div class="table"></div>
								</div>
							</div>

							<div id="adminMembers" class="adminBlock h-container" style="display:none;">
								<div class="h-auto" action="addMember" type="button">
									<div class="button active">Добавить игрока</div>
								</div>
								<div id="adminTableMembers" class="adminTable h-full">
									<div class="table"></div>
								</div>
								<div id="formAddMember">
									<div class="mask">
										<div class="form">
											<div class="block-find">
												<input type="text" placeholder="Ник игрока"/>
												<div action="findMember" type="button">
													<div class="button active">Найти</div>
												</div>
											</div>
											<div class="listFindedMembers"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<? }; ?>
			<div id="graphSheet" class="sheet" menu="graph" order="4">
				<div class="sheet-inner h-container">
					<div id="graphMenu" class="h-auto">
						<div id="graphMenuGame" class="menu" action="buttonGraphGame" type="buttonRadio">
							<div class="button game active" menu="wot"><img src="images/logo/logo_wot.png"/></div>
							<div class="button game" menu="wotb"><img src="images/logo/logo_wotb.png"/></div>
							<div class="button game" menu="wowp"><img src="images/logo/logo_wowp.png"/></div>
							<div class="button game" menu="wows"><img src="images/logo/logo_wows.png"/></div>
						</div>
						<div id="graphMenuParam" class="menu" action="buttonGraph" type="buttonRadio">
							<div class="button all active" menu="wins">% побед</div>
							<div class="button all" menu="xp">Ср. опыт</div>
							<div class="button all" menu="damage">Ср. дамаг</div>
							<div class="button wot" menu="effBS">Эфф. БС</div>
							<div class="button wot" menu="effWN">Эфф. WN</div>
							<div class="button wot" menu="effWN6">Эфф. WN6</div>
							<div class="button wot" menu="effWN8">Эфф. WN8</div>
							<div class="button all" menu="active">Активность</div>
						</div>
						<div id="graphPeriod" class="changeDate" type="period" start="2012-08-05"
							 action="graphPeriod()">
							<button class="rewind" step="-1" period="day">Предыдущий</button>
							<input type="text" name="date1" value="" class="inputDate" title=""/>
							<b>&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;</b>
							<input type="text" name="date2" value="" class="inputDate" title=""/>
							<button class="forward" step="1" period="day">Следующий</button>
						</div>
					</div>
					<div id="graphMain" class="h-full">
						<div id="graphList">
							<div class="table"></div>
						</div>
						<div id="graphics"><!--<svg></svg>--></div>
					</div>
				</div>
			</div>

			<div id="statSheet" class="sheet" menu="stat" order="5">
				<div class="sheet-inner h-container">
					<div class="h-auto">
						<div id="statMenuGame" class="menu" action="buttonStatGame" type="buttonRadio">
							<div class="button game active" menu="wot"><img src="images/logo/logo_wot.png"/></div>
							<div class="button game" menu="wotb"><img src="images/logo/logo_wotb.png"/></div>
							<div class="button game" menu="wowp"><img src="images/logo/logo_wowp.png"/></div>
							<div class="button game" menu="wows"><img src="images/logo/logo_wows.png"/></div>
						</div>
						<div id="statMenu" class="menu" action="buttonStat" type="buttonRadio">
							<div class="button active" menu="all">Общая</div>
							<div class="button" menu="eff">Эффективность</div>
							<div class="button" menu="medals">Награды</div>
							<div class="button" menu="technics">Техника</div>
						</div>
					</div>
					<div id="statTables" class="h-full">
						<div id="statAll">
							<div class="table"></div>
						</div>
						<div id="statEff">
							<div class="table"></div>
						</div>
						<div id="statMedals">

						</div>
						<div id="statTechnics" class="h-container">
							<div id="statTechnicsMenu" class="menu h-auto" action="statTechnics" type="buttonRadio">
								<div class="button active" menu="levels">По уровню</div>
								<div class="button" menu="tree">Дерево развития</div>
							</div>
							<div id="statTechnicsTable" class="statTechnics h-full">
								<div class="table"></div>
							</div>
							<div id="statTechnicsTree" class="statTechnics h-full"></div>
						</div>
					</div>
				</div>
				<!--<div id="statTables">
					<div id="statMain" class="statTable"><div id="tableStatMain" class="table"></div></div>
					<div id="statEffect" class="statTable"><div id="tableStatEffect" class="table"></div></div>
					<div id="statMedals">
						<div id="statMedalMenu" class="menu">
							<div id="buttonMedalBattle" class="button active" section="battle"><div class="hover"></div><div class="text">Герои<br />битвы</div></div>
							<div id="buttonMedalSpecial" class="button" section="special"><div class="hover"></div><div class="text">Почётные<br />звания</div></div>
							<div id="buttonMedalEpic" class="button" section="epic"><div class="hover"></div><div class="text">Эпические<br />медали</div></div>
							<div id="buttonMedalGroup" class="button" section="group"><div class="hover"></div><div class="text">Групповые<br />награды</div></div>
							<div id="buttonMedalMemorial" class="button" section="memorial"><div class="hover"></div><div class="text">Памятные<br />знаки</div></div>
							<div id="buttonMedalClass" class="button" section="class"><div class="hover"></div><div class="text">Этапные<br />награды</div></div>
							<div id="buttonMedalAction" class="button" section="action"><div class="hover"></div><div class="text">Особые</div></div>
						</div>
						<div id="medalSect" class="menu"></div>
						<div id="medalTables" class="medalTable">
							<div id="tableStatMedals" class="table"></div>
						</div>
					</div>
					<div id="statTanks">
						<div id="statTanksMenu" class="menu">
							<div id="buttonTanksLevel" class="button active"><div class="hover"></div><div class="text">По уровню</div></div>
							<div id="buttonTanksTree" class="button"><div class="hover"></div><div class="text">Дерево развития</div></div>
						</div>
						<div id="tanksTables">
							<div id="tanksLevel" class="tanksTable"><div id="tableStatsTanks" class="table"></div></div>
							<div id="tanksTree" class="tanksTable clearfix">
								<div id="treeListNations">
									<div id="inListNation">
										<div id="treeListMembers"><select onChange="resize()">
											<option selected="selected" value="all">Все</option>
										</select></div>
										<div class="listNation active" nation="ussr">
											<div class="listNameNation">СССР</div>
											<div class="listImageNation"><img src="images/Flags/flagUssr.png"></div>
										</div>
										<div class="listNation" nation="germany">
											<div class="listNameNation">Германия</div>
											<div class="listImageNation"><img src="images/Flags/flagGermany.png"></div>
										</div>
										<div class="listNation" nation="usa">
											<div class="listNameNation">США</div>
											<div class="listImageNation"><img src="images/Flags/flagUsa.png"></div>
										</div>
										<div class="listNation" nation="france">
											<div class="listNameNation">Франция</div>
											<div class="listImageNation"><img src="images/Flags/flagFrance.png"></div>
										</div>
										<div class="listNation" nation="uk">
											<div class="listNameNation">Великобритания</div>
											<div class="listImageNation"><img src="images/Flags/flagUk.png"></div>
										</div>
										<div class="listNation" nation="china">
											<div class="listNameNation">Китай</div>
											<div class="listImageNation"><img src="images/Flags/flagChina.png"></div>
										</div>
										<div class="listNation" nation="japan">
											<div class="listNameNation">Япония</div>
											<div class="listImageNation"><img src="images/Flags/flagJapan.png"></div>
										</div>
									</div>
								</div>
								<div id="tanksTreeImage">
									<span>
										<div id="tableTanksTree"></div>
										<div id="tableTanksTreeMask"></div>
									</span>
								</div>
							</div>
						</div>
					</div>
				</div>-->
			</div>
			<? if ($rights == 'member' || $rights == 'admin' || $rights == 'sadmin') { ?>
				<div id="eventSheet" class="sheet" menu="event" order="6">
					<div id="eventMain">
						<div id="eventMenu" class="changeDate" type="day" start="year" action="eventsCheckDate()">
							<button class="rewind" step="-1">Предыдущий</button>
							&nbsp;
							<input type="text" name="date" value="" class="inputDate" title=""/>
							&nbsp;
							<button class="forward" step="1">Следующий</button>
						</div>
						<!--<div id="eventEvents" class="adminBlock">
							<div id="eventShoping">
								<div class="borderBlock">
									<div class="headerBlock">Техника</div>
									<div class="infoBlock"></div>
								</div>
							</div>
							<div id="eventEvent">
								<div class="borderBlock">
									<div class="headerBlock">События</div>
									<div class="infoBlock"></div>
								</div>
							</div>
							<div id="eventArmors">
								<div class="borderBlock">
									<div class="headerBlock">Статусы</div>
									<div class="infoBlock"></div>
								</div>
							</div>
						</div>-->
					</div>
				</div>
			<? }; ?>
		</div>
	</div>
	<div id="message">
		<div id="messageBlock">
			<span id="messageText"></span>
			<div id="messageButtons">
				<div id="messageButtonCancel">
					<button>Нет</button>
				</div>
				<div id="messageButtonOk">
					<button>Да</button>
				</div>
			</div>
		</div>
	</div>
	<div id="wait" class="wait">
		<div class="spinner-mask"></div>
		<div class="spinner">
			<svg>
				<g stroke="#979899" stroke-width="15px" stroke-linecap="round">
					<line class="spinner-line1" x1="90" y1="50" x2="90" y2="10"></line>
					<line class="spinner-line2" x1="105.31" y1="53.04" x2="120.61" y2="16.09"></line>
					<line class="spinner-line3" x1="118.28" y1="61.72" x2="146.57" y2="33.43"></line>
					<line class="spinner-line4" x1="126.96" y1="74.69" x2="163.91" y2="59.39"></line>
					<line class="spinner-line5" x1="130" y1="90" x2="170" y2="90"></line>
					<line class="spinner-line6" x1="126.96" y1="105.31" x2="163.91" y2="120.61"></line>
					<line class="spinner-line7" x1="118.28" y1="118.28" x2="146.57" y2="146.57"></line>
					<line class="spinner-line8" x1="105.31" y1="126.96" x2="120.61" y2="163.93"></line>
					<line class="spinner-line9" x1="90" y1="130" x2="90" y2="170"></line>
					<line class="spinner-line10" x1="74.69" y1="126.96" x2="59.39" y2="163.93"></line>
					<line class="spinner-line11" x1="61.72" y1="118.28" x2="33.43" y2="146.57"></line>
					<line class="spinner-line12" x1="53.04" y1="105.31" x2="16.09" y2="120.61"></line>
					<line class="spinner-line13" x1="50" y1="90" x2="10" y2="90"></line>
					<line class="spinner-line14" x1="53.04" y1="74.69" x2="16.09" y2="59.39"></line>
					<line class="spinner-line15" x1="61.72" y1="61.72" x2="33.43" y2="33.43"></line>
					<line class="spinner-line16" x1="74.69" y1="53.04" x2="59.39" y2="16.09"></line>
				</g>
			</svg>
		</div>
	</div>
	</body>
	</html>
<?
function toLog($data)
{
	$dir = 'utils/log';
	if (!(file_exists($dir))) mkdir($dir);
	$name = $dir . '/visitors.txt';
	$fp = fopen($name, 'a');
	fwrite($fp, str_replace('<br />', PHP_EOL, $data));
	fclose($fp);
}

?>
