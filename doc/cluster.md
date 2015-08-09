# Clustering Queues

With simpleq you are able to cluster queues to multiple servers, by simply configuring the queue on multiple servers, 
split up your job persists to the different queues and build up your workers to send final data to the same target.

1. Custom Persist Service
- persist
2. QueueServer1|QS2|QSn
- scheduler triggers
3. Custom Worker Service
- does job and handles result
4. Custom Target