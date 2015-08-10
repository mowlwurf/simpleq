# Clustering Queues

With Simpleq you are able to cluster queues to multiple servers, by simply configuring the queue on multiple servers, 
split up your job persists to different queues and build up your workers to send the final data to one and the same target.

1. Custom Persist Service
- persist job
2. QueueServer1|QS2|QSn
- scheduler triggers
3. Custom Worker Service
- does job and handles result
4. Custom Target