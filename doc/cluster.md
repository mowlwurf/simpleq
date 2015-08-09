# Clustering Queues

With simpleq you are able to cluster queues to multiple servers, by simply configuring the queue on multiple servers, 
split up your job persists to the different queues and build up your workers to send final data to the same target.

Custom Persist -> One simpleq queue config on multiple working servers -> Custom Worker provides data to custom target 