<?
//헤더
//DB 연결
include "header.php";
include "db_info.php";
//주행 조건을 쿼리에 추가한다.
if ($_GET['service'] == "yes") {
$add= "where service= 'yes'";}
elseif ($_GET['service'] == "no") {	
$add= "where service= 'no'";
}

$query = "SELECT * FROM carInfo INNER JOIN carnum ON carInfo.num = carnum.carid $add order by state desc, pred asc, bat asc";
$query3 = "SELECT * FROM carInfo where service='yes' order by pred asc, bat asc";
$result = mysql_query($query, $conn); 
//정보창 표시용
$result2 = mysql_query($query, $conn);  //네이버 지도 차량 표시용
$result3 = mysql_query($query3, $conn); //네이버 지도 주차 차량 동선 및 티맵 API 용
$num = 1;
$park_count = mysql_num_rows($result3);  //주차 차량
$car_count = mysql_num_rows($result);   //모든 차량
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!meta http-equiv='refresh' content='10';>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <title>HYUNDAI MOTORS EV Battery Charge</title>
    <script type="text/javascript" src="https://openapi.map.naver.com/openapi/v3/maps.js?clientId=zfGDFt_miJ8FZG7V80s9"></script>
</head>
<body>
<div class="content-wrapper" style="top:60px;">

<div class="pure-g">
<!--네이버 지도 출력할 DIV 생성-->

<div id="map" class="pure-u-1 pure-u-md-2-3"></div>
<div id="info" class="pure-u-1 pure-u-md-1-3">

<!--티맵 API 출력-->
<table class="pure-table pure-table-horizontal" style="width:100%;text-align:center;">

	<thead>
	
        <tr>
            <th colspan="5" id="time"></th>
  
        </tr>
    </thead>

    <tbody>
	        <tr style="color:black;font-weight:bold;">
            <td>차량 번호</td>
            <td>현재 위치</td>
            <td>주행 상태</td>
            <td> 배터리 </td>
			<td>예상 주차시간</td>
        </tr>
	<?
while($row=mysql_fetch_array($result))
{
?>
        <tr>
            <td><?=$row[carnumber]?></td>
            <td id="ad<?=$num?>"></td>
            <td><? if ($row[state] =="drive") {
								echo "주행 중"; }
								else { echo "주차 중"; 
								}	?></td>
            <td><?=$row[bat]?>%</td>
			<td><?=$row[pred]?></td>
			</tr>
<?
$num = $num +1;
} 
$num =1;
?>
			
			</tbody>
</table>
</div>



</div>


<!--네이버 지도 API 호출-->
<script>
var HOME_PATH = window.HOME_PATH || '.';
var map = new naver.maps.Map('map', {
    center: new naver.maps.LatLng(37.51694, 126.91750),
     scaleControl: false,
        logoControl: false,
        mapDataControl: false,
        zoomControl: true,
        zoom: 9,
	mapTypeControl: true,
});

var trafficLayer = new naver.maps.TrafficLayer({
    interval: 2000 // 2초마다 새로고침
});

//영등포구청 전기차 충전소
var marker<?=$num?> = new naver.maps.Marker({
    position: new naver.maps.LatLng(37.5247471, 126.8954544),
    title: 'A',
	   map: map,
    icon: {
        content: '<div><img src="'+ HOME_PATH +'/img/station.png" alt="" ' +
                 'style="margin: 0px; padding: 0px; border: 0px solid transparent; display: block; max-width: none; max-height: none; ' +
                 '-webkit-user-select: none; position: relative; width: 50px; height: 50px; left: 0px; top: 0px;"></div>',
        size: new naver.maps.Size(22, 35),
        anchor: new naver.maps.Point(11, 35)
    }
});


<?
while($row=mysql_fetch_array($result2))
{
?>



var marker<?=$num?> = new naver.maps.Marker({
    position: new naver.maps.LatLng(<?=$row[lat]?>, <?=$row[lon]?>),
    title: 'A',
	   map: map,
    icon: {
        content: '<div><img src="'+ HOME_PATH +'/img/<?=$row[service]?>.png" alt="" ' +
                 'style="margin: 0px; padding: 0px; border: 0px solid transparent; display: block; max-width: none; max-height: none; ' +
                 '-webkit-user-select: none; position: relative; width: 50px; height: 30px; left: 0px; top: 0px;"></div><div style="margin: 0px; padding: 0px; border: 0px solid transparent; display: block; max-width: none; max-height: none; ' +
                 '-webkit-user-select: none; position: absolute; width: 50px; height: 30px; left: 0px; top: 10px; text-align:center; color:white; font-size: 12px"><?=$row[bat]?></div>',
        size: new naver.maps.Size(22, 35),
        anchor: new naver.maps.Point(11, 35)
    }
});




var infowindow<?=$num?> = new naver.maps.InfoWindow({
    content: '<div><?=$row[carnumber]?> </div>',
	maxWidth: 0,
    backgroundColor: "#eee",
    borderColor: "#2db400",
    borderWidth: 1,
    anchorSize: new naver.maps.Size(30, 30),
    anchorColor: "#eee",
});

naver.maps.Event.addListener(marker<?=$num?>, "click", function(e) {
    if (infowindow<?=$num?>.getMap()) {
        infowindow<?=$num?>.close();
    } else {
        infowindow<?=$num?>.open(map, marker<?=$num?>);
    }
});


<?
$num = $num +1;
} 
$num =1;
?>
infowindow1.open(map, marker1);
//trafficLayer.setMap(map);

//충전 서비스 운행 라인을 맵에 표시

</script>
<!--네이버 지도 API 끝-->


<!-- 다중경로 티맵 API 콜을 위한 변수 및 경로리스트 작성 -->
<?
$appkey="d47869af-465d-38ec-8d7a-99b625292a24";
for ($x = 0; $x <= $park_count -1; $x++) {
$passlistX[$x] = mysql_result($result,$x,2);
$passlistY[$x] = mysql_result($result,$x,1);
}
?>
<?
for ($x = 1; $x <= $park_count -3; $x++) {
$passlist = $passlist.$passlistX[$x].",".$passlistY[$x]."_";
}
$passlist = $passlist.$passlistX[$park_count -2].",".$passlistY[$park_count -2];
?>
<!--티맵 API 시작-->
<script type = "text/javascript" 
         src = "http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		

	  
<script>
var tmapAPI = "https://apis.skplanetx.com/tmap/routes?version=1&reqCoordType=WGS84GEO&startX="
tmapAPI +="<?=$passlistX[0]?>&startY=<?=$passlistY[0]?>&endX=<?=$passlistX[$park_count -1]?>&endY="
tmapAPI +="<?=$passlistY[$park_count -1]?>&appKey=<?=$appkey?>&passList=<?=$passlist?>";

 $.getJSON(tmapAPI, function (json) {
var time = json.features[0].properties.totalTime
var distance = json.features[0].properties.totalDistance
$('#time').text("총 소요시간: "+parseInt(time/60)+"분"+" "+time%60+"초"+", 총 거리: "+(distance/1000).toFixed(1)+" km");
});
</script>

<!--역Geo코딩 호출 -->
<?
for ($x = 0; $x <= $car_count -1; $x++) {
$carlistX[$x] = mysql_result($result,$x,2);
$carlistY[$x] = mysql_result($result,$x,1);
?>
<script>
var reversegeoAPI = "https://apis.skplanetx.com/tmap/geo/reversegeocoding?version=1&format=json&appKey=<?=$appkey?>&addressType=A10&lat=<?=$carlistY[$x]?>&lon=<?=$carlistX[$x]?>&coordType=WGS84GEO";

 $.getJSON(reversegeoAPI, function (json) {
var ad = json.addressInfo.legalDong+" "+json.addressInfo.buildingName;
$('#ad<?=$x?>').text(ad);
});
</script>
<?
}
?>
  
<!--티맵 API 끝-->


<div class="footer l-box is-center">
       <a> HYUNDAI MOTOR GROUP _ The First Hackathon</a></br><a>CONNECT THE</a> <a style="text-decoration: overline;">un</a><a>CONNECTED _ Team UDT </a>
    </div>
</div>

</div>

</body>

</html>