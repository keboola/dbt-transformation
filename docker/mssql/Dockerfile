# workaround: mssql-server or latest azure-sql-edge does not work on M1 for now
FROM --platform="linux/arm64/v8" mcr.microsoft.com/azure-sql-edge:1.0.6

EXPOSE 1433
COPY init.sql init.sql

ENV SA_PASSWORD Passw@rd
ENV SQLCMDPASSWORD Passw@rd
ENV ACCEPT_EULA Y

# workaround: mssql tools has to be installed separately on M1 for now
RUN (mkdir -p /opt/mssql-tools/bin  \
    && cd /opt/mssql-tools/bin  \
    && wget https://github.com/microsoft/go-sqlcmd/releases/download/v0.8.0/sqlcmd-v0.8.0-linux-arm64.tar.bz2 \
    && bzip2 -d sqlcmd-v0.8.0-linux-arm64.tar.bz2 && tar -xvf sqlcmd-v0.8.0-linux-arm64.tar && chmod 755 sqlcmd)

RUN  /opt/mssql/bin/sqlservr & sleep 30  \
     && /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -d master -Q "create database test;"  \
     && /opt/mssql-tools/bin/sqlcmd -S localhost -U sa -d master -i init.sql

CMD /opt/mssql/bin/sqlservr