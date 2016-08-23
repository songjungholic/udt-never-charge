<?
//헤더
//DB 연결
include "header.php";
include "db_info.php";
//주행 조건이 있는지 확인하고 쿼리문에 추가할 텍스트 할당
if ($_GET['state'] == "") {
$add= "";}
	else {	
$add= "where state= '".$_GET['state']."'";
}

$query = "SELECT * FROM carInfo $add order by state desc, pred asc";
$query3 = "SELECT * FROM carInfo where state='stop' and pred > 1 order by pred";
$result = mysql_query($query, $conn); //정보창 표시용
$result2 = mysql_query($query, $conn);  //네이버 지도 차량 표시용
$result3 = mysql_query($query3, $conn); //네이버 지도 주차 차량 동선 및 티맵 API 용
$num = 1;
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
<div class="content-wrapper" style="top:7%;">

<div class="pure-g">
<!--네이버 지도 출력할 DIV 생성-->

<div id="map" class="pure-u-1 pure-u-md-2-3"></div>
<div id="info" class="pure-u-1 pure-u-md-1-3">

<!--티맵 API 출력-->
<div class="pure-g">
    <div class="pure-u-1-1"><p id="time" style="font-weight:bold;color:black;"></p></div>
</div>
<div class="pure-g" style="background-color:gray; color:white;">
    <div class="pure-u-1-4"><p>차량번호</p></div>
    <div class="pure-u-1-4"><p>주행상태</p></div>
    <div class="pure-u-1-4"><p>배터리</p></div>
	<div class="pure-u-1-4"><p>남은 주차시간</p></div>
</div>
<?
while($row=mysql_fetch_array($result))
{
?>
<div class="pure-g">
    <div class="pure-u-1-4"><p><?=$num?></p></div>
    <div class="pure-u-1-4"><p><?=$row[state]?></p></div>
    <div class="pure-u-1-4"><p><?=$row[bat]?>%</p></div>
	<div class="pure-u-1-4"><p><?=$row[pred]?></p></div>
</div>
<?
$num = $num +1;
} 
$num =1;
?>
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
        content: '<div><img src="'+ HOME_PATH +'/img/<? if ($row[state] == drive) {
	  echo "drive"	; }
	  else {
	  echo "stop";}

?>.png" alt="" ' +
                 'style="margin: 0px; padding: 0px; border: 0px solid transparent; display: block; max-width: none; max-height: none; ' +
                 '-webkit-user-select: none; position: relative; width: 50px; height: 30px; left: 0px; top: 0px;"></div><div style="margin: 0px; padding: 0px; border: 0px solid transparent; display: block; max-width: none; max-height: none; ' +
                 '-webkit-user-select: none; position: absolute; width: 50px; height: 30px; left: 0px; top: 10px; text-align:center; color:white; font-size: 12px"><?=$row[bat]?></div>',
        size: new naver.maps.Size(22, 35),
        anchor: new naver.maps.Point(11, 35)
    }
});




var infowindow<?=$num?> = new naver.maps.InfoWindow({
    content: '<div><? if ($row[state] == drive) {
	  echo "주행 중"	; }
	  else {
	  if ($row[pred] < 1) {
      echo $row[pred],"1시간 내 주행 예정" ;}
	  else {
		echo " 시간 주차 예정";
	  }
	  } ?> </div>',
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

// infowindow<?=$num?>.open(map, marker<?=$num?>);
<?
$num = $num +1;
} 
$num =1;
?>

//trafficLayer.setMap(map);


//충전 서비스 운행 라인을 맵에 표시
var polyline = new naver.maps.Polyline({
    map: map,
    path: [
       new naver.maps.LatLng(37.5247471, 126.8954544),
	<?
while($row=mysql_fetch_array($result3))
{
?> 
 new naver.maps.LatLng(<?=$row[lat]?>, <?=$row[lon]?>),	
<?
$num = $num +1;
} 
$num =1;
?>
    ]
});

</script>
<!--네이버 지도 API 끝-->

<!--거리 계산 & 소요 시간 계산 - 티맵 API 이용을 위한 변수 설정-->
<?
$car_count = mysql_num_rows($result);
$startX = mysql_result($result,0,2);
$startY = mysql_result($result,0,1);
$endX = mysql_result($result,$car_count -1,2);
$endY = mysql_result($result,$car_count -1,1);
?>



<? echo "startX".$startX.", startY".$startY ?>
<? echo "endX".$endX.", endY".$endY ?>
<!--티맵 API 시작-->
<script type = "text/javascript" 
         src = "http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
		
 <script type = "text/javascript" language = "javascript">
 
var params = "version=1&reqCoordType=WGS84GEO"
params += "&startX=<?=$startX?>&startY=<?=$startY?>"
params += "&endX=<?=$endX?>&endY=<?=$endY?>&appKey=d47869af-465d-38ec-8d7a-99b625292a24"

$.ajax({ 
type : "POST",
url : "https://apis.skplanetx.com/tmap/routes",
data : params,
dataType:"JSON",
success : function(data) { 
var time = data.features[0].properties.totalTime
var distance = data.features[0].properties.totalDistance
if (time < 3600) {
$('#time').text("총 소요시간: "+parseInt(time/60)+"분"+" "+time%60+"초"+", 총 거리: "+distance); }  //시간을 분,초 로 표시한다
}, 
error : function(xhr, status, error) { 
alert(error) 
} 
});
      </script>
<!--티맵 API 끝-->
</div>
</div>

</body>

</html>