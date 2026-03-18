import logging
import os

_log_file = os.environ.get("SNOWFLAKE_CONNECTOR_LOG_FILE")
if _log_file:
    _level = os.environ.get("SNOWFLAKE_CONNECTOR_LOG_LEVEL", "WARNING").upper()
    _handler = logging.FileHandler(_log_file)
    _handler.setFormatter(logging.Formatter("%(asctime)s %(name)s %(levelname)s: %(message)s"))
    _logger = logging.getLogger("snowflake.connector")
    _logger.addHandler(_handler)
    _logger.setLevel(getattr(logging, _level, logging.WARNING))
