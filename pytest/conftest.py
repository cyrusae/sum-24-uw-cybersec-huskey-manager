import os
import pytest
import pymysql
from dotenv import load_dotenv

#load env variables 
load_dotenv()

@pytest.fixture(scope='module')
def db_connection():
 connection = pymysql.connect(
  host='localhost',
  user=os.getenv('MYSQL_USER'),
  password=os.getenv('MYSQL_PASSWORD'),
  db=os.getenv('MYSQL_DATABASE')
 )
 yield connection
 connection.close()
