library(lubridate)
library(RMySQL)
library(nnet)

mydb = dbConnect(MySQL(), user='udt', password='udt', dbname='udt', host='52.78.129.66')

# get data from SQL server. (or this folder)
files<-dbGetQuery(mydb, "show tables like '%learn'")[,1]
for (i in files){
  routes<-paste("select * from",i)
  assign(i, dbGetQuery(mydb, routes) )
}

# normalization
for (j in 1:8){
  assign(paste("car",j,"_learn_lat_max",sep=""),
         max(get(paste("car",j,"_learn" ,sep=""))$lat) )
  assign(paste("car",j,"_learn_lat_min",sep=""),
         min(get(paste("car",j,"_learn" ,sep=""))$lat) )
  assign(paste("car",j,"_learn_lon_max",sep=""),
         max(get(paste("car",j,"_learn" ,sep=""))$lon) )
  assign(paste("car",j,"_learn_lon_min",sep=""),
         min(get(paste("car",j,"_learn" ,sep=""))$lon) )
  assign(paste("car",j,"_learn_parking_max",sep=""),
         max(get(paste("car",j,"_learn" ,sep=""))$parking) )
}

# ANN learning
for (j in 1:8){
  assign(paste("car",j,"_fit",sep=""),
         nnet(parking/get(paste("car",j,"_learn_parking_max",sep=""))~., 
              data= get(paste(  "car_norm",j,"_learn"  ,sep="")), 
              size=6,
              linout=FALSE, 
              decay=5e-4, 
              maxit = 200))
}

