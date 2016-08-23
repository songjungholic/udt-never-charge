
# result - successes of delivery charging module

#myData <- normalized second data
#myNorm <- second data

inData <- myData
max_lat <- max(inData$lat)
max_lon <- max(inData$lon)
min_lat <- min(inData$lat)
min_lon <- min(inData$lon)

max_park <- max(inData$parking)

latReg <-function(inP) { (inP-min_lat)/(max_lat - min_lat)}
lonReg <- function(inP) { (inP-min_lon)/(max_lon - min_lon)}

#
#

testdata <- myNorm

nnet.fit <- nnet(parking/max_park~., data=testdata[1:12000,], size=10,
                 linout=FALSE, decay=5e-4, maxit = 300)

#learning 66% data
nnet.predict <- predict(nnet.fit, testdata[-(1:12000),]) * (max_park)
car4 <- data.frame(testdata[-(1:12000),], nnet.predict)


car5<-car4[car4$nnet.predict >=2.5 ,]
results<-sum(( 2.5 <= car5$parking))/length(car5[,1])
results2<- sum(car4$parking>=2.5 & car4$nnet.predict>=2.5)/sum(car4$parking>=2.5)

print(results)
