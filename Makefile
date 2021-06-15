CURRENT_DIR := $(shell pwd)
DEPLOYER=artifactory.wikia-inc.com/ops/k8s-deployer:0.0.29
IMAGE=artifactory.wikia-inc.com/aden/dfp-query-tool
TAG=0.0.4

# Build Docker image
build:
	docker build -t ${IMAGE}:${TAG} -f k8s-cron-jobs/Dockerfile .

# push image to artifactory
push:
	docker push ${IMAGE}:${TAG}

# private
# appends CMD parameter to kubectl command
exec:
	docker run -it --rm -v "${CURRENT_DIR}/k8s-cron-jobs/dfp-query-tool-poz-dev.yaml":/cronjob.yaml ${DEPLOYER} kubectl -n dev --context=kube-poz-dev ${CMD}

deploy:
	$(MAKE) exec CMD="-f /cronjob.yaml create"

delete:
	$(MAKE) exec CMD="-f /cronjob.yaml delete"

