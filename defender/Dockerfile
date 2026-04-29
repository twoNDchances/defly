FROM golang:1.26.1-alpine AS builder

WORKDIR /app

COPY go.mod go.sum ./
RUN go mod download

COPY . .
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o /bin/defender ./cmd/defender

FROM alpine:3.22

RUN apk add --no-cache ca-certificates \
    && addgroup -S defender \
    && adduser -S -D -H -G defender defender

WORKDIR /app

COPY --from=builder /bin/defender /app/defender
COPY configs /app/configs
COPY storage /app/storage

RUN mkdir -p /app/storage/errors /app/storage/logs /app/storage/data /app/storage/tls \
    && chown -R defender:defender /app

EXPOSE 9947 9948

USER defender

ENTRYPOINT ["/app/defender"]
