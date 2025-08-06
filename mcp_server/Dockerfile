ARG NODE_IMAGE=node:22-slim
ARG NGINX_IMAGE=nginx:stable-alpine

# Development
FROM ${NODE_IMAGE} AS base
WORKDIR /app
RUN apt update -y && apt upgrade -y

FROM base AS build
COPY ./mcp_server/ ./
# RUN npm install

# Definir las variables de entorno como build args para que estén disponibles durante el build
# ARG VITE_API_URL
# ENV VITE_API_URL=$VITE_API_URL

# RUN npm run build

# Production
FROM ${NGINX_IMAGE} AS production
WORKDIR /usr/share/nginx/html

# Copiar la configuración de Nginx
COPY ./mcp_server/nginx.conf /etc/nginx/conf.d/default.conf

# Copiar los archivos compilados desde la etapa de build
COPY --from=build /app/build /usr/share/nginx/html

# Exponer el puerto 80
EXPOSE 80

# Iniciar Nginx
CMD ["nginx", "-g", "daemon off;"]
