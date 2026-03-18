FROM node:20-alpine AS build

WORKDIR /app/frontend

COPY frontend/package*.json ./
RUN npm ci

COPY frontend ./
RUN npm run build

FROM node:20-alpine AS runtime

WORKDIR /app

ENV NODE_ENV=production
ENV STATIC_ROOT=/app/frontend/out

COPY --from=build /app/frontend/out ./frontend/out
COPY --from=build /app/frontend/scripts/serve-static.mjs ./frontend/scripts/serve-static.mjs

EXPOSE 8080

CMD ["node", "frontend/scripts/serve-static.mjs"]
