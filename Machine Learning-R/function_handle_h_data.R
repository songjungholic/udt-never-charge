#
# set working directory for H data
#
# setwd("~/hackathon_test_data/data2/D")
# setwd("~/hackathon_test_data/data2/F")
# setwd("~/hackathon_test_data/data2/H")
#
# we can get second data from raw data(h_data)
#
# results <- second data for learning (from sum data)
# trace <- jpg file of position trace for time
# sum <- integrate and filter raw data 

getFolderResults <- function(start="2016-04-01", out="2016-06-30", cutMin=30) {
  library(lubridate)
  Sys.setlocale("LC_TIME", "C")
  
  routeName<-getwd()
  folders<-dir()
  
  for ( fold in folders){
  setwd( paste(routeName,"/",fold,sep="") )
  
  ####################################################
  # TO GET integrated data log
  ####################################################
  #
  # To integrate all files of specific group_key in the folder.
  #
  # START
  
  startTime <- ymd_hms(paste(start,"00:00:00"))
  outTime <- ymd_hms(paste(out,"23:59:59"))
  
  # to work in target folder.
  files<-dir(pattern = "log.csv")
  filesTime <- ymd_hms(files)
  getIndex_1 <- (filesTime > startTime) & (filesTime < outTime)
  getFiles <- files[getIndex_1]
  
  # allocate space
  sumData <- data.frame()
  
  for ( i in getFiles ) {
    process_1 <- read.csv(file=i, stringsAsFactors=FALSE, header = TRUE, fileEncoding = "CP949")
    
    #get 'effective' and 'needed' location data
    getIndex_2 <- (!is.na(process_1$c_3)) & (!is.na(process_1$c_4)) 
    getIndex_3 <- c("c_0","c_2","c_3","c_4")
    process_2 <- process_1[getIndex_2,getIndex_3]
    
    engTime <- ymd_hms(i)
    process_2$c_2 <- ymd_hms(i) + process_2$c_2
    
    sumData <- rbind(sumData, process_2)
  }
  
  #to order data according to increasing time
  process_3 <- sumData[order(sumData$c_2),]
  
  #detect and remove outlier
  process_3<-process_3[abs(scale(process_3$c_3)) < 10 & abs(scale(process_3$c_4)) < 10, ]
  
  
  # WRITE.CSV : integrated discrete-data log
  startString<- format(as.Date(startTime),format="_%y%m%d")
  outString<- format(as.Date(outTime),format="_%y%m%d")
  fileName <- paste(routeName,"/sum",startString,outString,"_cut_",as.character(cutMin),
                    "_group_",fold,".csv",sep="")
  write.csv( process_3, file = fileName, row.names = FALSE)
  
  
  ####################################################
  # TO GET : data log with 'parking' to learn machine
  ####################################################
  #
  # 'parking' means available parking time wih specific location and time.
  #
  # START
  
  # initiation
  firstTime <- process_3$c_2[1]
  endTime <- process_3$c_2[length(process_3$c_2)]
  settingTime <- ymd_hms(paste(as.Date(firstTime), "00:00:01"))
  countScale <-ceiling((as.numeric(firstTime) - as.numeric(settingTime))/(cutMin*60))
  countTime <- settingTime + countScale*60*cutMin
  iter <- floor((as.numeric(endTime) - as.numeric(countTime))/(60*cutMin))+1
  
  #allocation
  getScale <- (24*60)/cutMin
  period <- c()
  lat <- c()
  lon <- c()
  time <- c()
  parking <- c()
  
  timeIndex <- process_3$c_2
  latIndex <- process_3$c_3
  lonIndex <- process_3$c_4
  
  #loop
  for (j in 1:iter) {
    loopIndex <- sum(timeIndex<=countTime)
    
    time[j] <- as.character(countTime)
    period[j] <- countScale
    lat[j] <- latIndex[loopIndex]
    lon[j] <- lonIndex[loopIndex]
    
    countTime <- countTime + cutMin*60
    countScale <- countScale + 1
  }
  
  parking[iter] <- NA
  for (k in (iter-1):1) {
    if( abs(lat[k]-lat[k+1])<0.0000001 & abs(lon[k]-lon[k+1])<0.000001) {
      parking[k]<-parking[k+1]+1
    } else {
      parking[k]<-0
    }
  }
  
  #generate data.frame
  process_4 <- data.frame(time=ymd_hms(time),
                          period=period%%getScale,
                          lat=lat,
                          lon=lon,
                          parking= parking*cutMin/60)
  
  # remove unknown 'parking' and gain effective data
  process_5 <- process_4[!is.na(process_4$parking),]
  
  # WRITE.CSV : continuous-data log containing 'parking' time 
  startString<- format(as.Date(startTime),format="_%y%m%d")
  outString<- format(as.Date(outTime),format="_%y%m%d")
  #fileName <- paste("results",startString,outString,"_cut_",as.character(cutMin),".csv",sep="")
  
  fileName <- paste(routeName,"/results",startString,outString,"_cut_",as.character(cutMin),
                    "_group_",fold,".csv",sep="")
  
  write.csv( process_5, file = fileName, row.names = FALSE)
  
  ####################################################
  # TO GET : summary plot
  ####################################################
  #
  # START
  lat_quant <-quantile(process_5$lat)
  lon_quant <-quantile(process_5$lon)
  
  lat_box <- (lat_quant[4] - lat_quant[2])*3
  lon_box <- (lon_quant[4] - lon_quant[2])*3
  
  plot(x=process_5$lat, y=process_5$lon,
       lwd=0)
       
  lines( process_5$lat, process_5$lon, col="#bb4cd4", lwd=2)     
  

  fileName <- paste(routeName,"/trace",startString,outString,"_cut_",as.character(cutMin),
                      "_group_",fold,".jpg",sep="")
    
  dev.copy(device = jpeg, filename = fileName, width = 800, height = 600)
  dev.off()
       
  }
  setwd(routeName)
}