# update predict data to SQL server.

library(lubridate)
library(RMySQL)
library(nnet)

updateCar <- function(){
  
  # update predict data to SQL server.
  
  query <- dbGetQuery(mydb, "show tables like 'car%log%'")
  carLists <- query[,1, drop=TRUE]
  
  carInfo <- data.frame()
  nowTime <- ymd_hms(Sys.time())
  nowPeriod <- checkPeriod()
  
  for (j in 1:length(carLists) ) {
    sqlAsks <- paste("select * from",carLists[j])
    iCar<-dbGetQuery(mydb, sqlAsks)
    
    getInfo <- iCar[ sum((nowTime-ymd_hms(iCar$time)) > 0) ,]
    past_15 <- iCar[ sum(( (nowTime-600) -ymd_hms(iCar$time)) > 0) ,]
    past_30 <- iCar[ sum(( (nowTime-1200)-ymd_hms(iCar$time)) > 0) ,]
    
    deltaDist <- abs(getInfo$lon - past_15$lon) + abs(getInfo$lat - past_15$lat) +
      abs(past_15$lon - past_30$lon) + abs(past_15$lat - past_30$lat)
    
    if (deltaDist < 0.00001 ) {
      
      
      
      x_per<- cos((nowPeriod/48)*pi*2)
      y_per<- sin((nowPeriod/48)*pi*2)
      lat_reg <- (getInfo$lat-get(paste("car",j,"_learn_lat_min",sep="")))/( get(paste("car",j,"_learn_lat_max",sep="")) - get(paste("car",j,"_learn_lat_min",sep="")) )
      
      lon_reg <- (getInfo$lon-get(paste("car",j,"_learn_lon_min",sep="")))/( get(paste("car",j,"_learn_lon_max",sep="")) - get(paste("car",j,"_learn_lon_min",sep="")) )
      
      
      inPut_reg <- data.frame(x_per=x_per, y_per=y_per, lat=lat_reg, lon=lon_reg)
      
      pred_exact <- as.numeric( predict( get(paste("car",j,"_fit",sep="")) ,inPut_reg) )*
        get(paste("car",j,"_learn_parking_max",sep=""))
      
      pred <- round(pred_exact,1)
      states <- "stop"
      service <- ifelse(pred_exact >=2.5 & round(getInfo$bat,1) < 75, "yes", "no")
      
      #pred <- as.numeric(predict(nnet.fit,inPut_reg)) #Use Neural Network
      #parking <- expectParkingTime()
    } else {
      states <- "drive"
      pred <- 0
      service <- "no"
    }
    
    getInfo$bat <- round(getInfo$bat,1)
    getInfo$state <- states
    getInfo$pred <- pred
    getInfo$service <- service
    
    carInfo <- rbind(carInfo, getInfo)
  }
  dbWriteTable(mydb, name='carInfo', value=carInfo, row.names=FALSE, overwrite = TRUE)
  dbGetQuery(mydb, "select * from carInfo")[1:10,]
}

checkPeriod <- function(min=30) {
  originSec<- as.numeric(ymd_hms(paste(as.Date(Sys.time()),"00:00:00")))
  currentSec<-as.numeric(ymd_hms(Sys.time()))
  ceiling((currentSec-originSec)/(60*min))
}