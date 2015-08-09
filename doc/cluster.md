# Clustering Queues

With simpleq you are able to cluster queues to multiple servers, by simply configuring the queue on multiple servers, 
split up your job persists to the different queues and build up your workers to send final data to the same target.

Custom Persist Service
    |
 |persist|
QS1     QS2
 |       |
worker  worker
 |finaldata|
     |
Custom Target