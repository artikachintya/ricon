import cv2
import os
import json
import mysql.connector
import numpy as np
from insightface.app import FaceAnalysis

DB_PATH = os.path.abspath("../../../public/images/my_db")
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ricon'
}

app = FaceAnalysis(name='buffalo_l', providers=['CUDAExecutionProvider'])
app.prepare(ctx_id=0, det_size=(640, 640))

def enroll():
    conn = None
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        print(f"Connected to database. Scanning: {DB_PATH}")

        for file in os.listdir(DB_PATH):
            if file.lower().endswith(('.jpg', '.png', '.jpeg')):
                img_path = os.path.join(DB_PATH, file)
                img = cv2.imread(img_path)
                person_name = os.path.splitext(file)[0]

                faces = app.get(img)
                if faces:
                    # Flattening embedding to ensure it's a simple list
                    embedding_list = faces[0].normed_embedding.tolist()
                    embedding_json = json.dumps(embedding_list)

                    query = "UPDATE users SET face_embedding = %s WHERE name = %s"
                    cursor.execute(query, (embedding_json, person_name))

                    if cursor.rowcount == 0:
                        print(f"[-] User '{person_name}' not found in DB. Skip.")
                    else:
                        print(f"[+] Enrolled {person_name}")

        conn.commit()
        print("\nEnrollment Process Finished.")

    except Exception as e:
        print(f"Error: {e}")
    finally:
        if conn:
            conn.close()

if __name__ == "__main__":
    enroll()
