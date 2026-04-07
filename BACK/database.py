import mysql.connector

def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",  # adapte selon ta config
        database="lido_serena"
    )