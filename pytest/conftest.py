"""
This module contains shared fixtures.
"""

import os
import pytest
import pymysql
from selenium import webdriver 
from selenium.webdriver.chrome.service import Service as ChromeService
# from webdriver_manager.chrome import ChromeDriverManager
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


@pytest.fixture(scope='module')
# Code based on https://github.com/AutomationPanda/tau-intro-selenium-py
def browser():
 b = webdriver.Chrome()
 b.implicitly_wait(10)
 yield b
 b.quit()