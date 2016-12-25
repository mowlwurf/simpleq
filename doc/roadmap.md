# Roadmap

***

| Vision | Version | Status  | Note |
|--------|---------|---------|------|
| first running prototype | (v0.1-alpha) | :moyai: | including processing bugs and memory leaks |
| removed scheduler process conflicts, by moving worker registration and job status trigger to scheduler | (v0.2-alpha) | :ballot_box_with_check: | no processing bugs |
| optimized scheduler process, added indices, deactivated doctrine logging and profiling | (v0.3-alpha) | :ballot_box_with_check: | no memory leaks |
| performance optimization of scheduler | (v0.4-alpha) | :rocket: | x100 faster, xn cheaper |
| queue or worker config attribute retry int $times | (v0.5-alpha) | :ballot_box_with_check: | failed jobs stay in queue to enable re-queuing |
| enable queue & config task handling | (v0.5-alpha) | :ballot_box_with_check: | got resolved one version earlier |
| testing db for phpunit | (v0.5-alpha) | :ballot_box_with_check: | providers fully tested now |
| queue option history to create and use job queue history | (v0.6-alpha) | :ballot_box_with_check: | |
| queue option delete_on_failure to enable custom handling for failed jobs | (v0.6-alpha) | :ballot_box_with_check: | |
| enable chainbehaviour | (v0.7-beta) | :link: | doc extension follows |
| performance optimization workerinterface/baseworker | (v0.8-beta) | :trophy: | worker interface performance optimized by factor 6-7 |
| project badges, code coverage, extended doc | (v0.9-beta) | :ballot_box_with_check: | including travis and coveralls configs |
| added job builder service | (v0.9-beta) | :ballot_box_with_check: | |
| full featured stable release | (v1.0) | :trophy: | |
| worker parameter retries | (v1.1) | :construction: | |
| webinterface to show queue,worker & scheduler status | (v1.5) | :grey_question: | maybe extra package |
