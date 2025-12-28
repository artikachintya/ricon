import cv2
import numpy as np
import json
import os
import mysql.connector
import qrcode
import uuid
from flask import Flask, request, jsonify
from flask_cors import CORS
from insightface.app import FaceAnalysis
from scipy.spatial.distance import cosine

app = Flask(__name__)
CORS(app)

# --- 1. CONFIGURATION ---
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'ricon'
}

# --- 2. INITIALIZE MODELS ---
# Optimized for your RTX 3050
app_model = FaceAnalysis(name='buffalo_l', providers=['CUDAExecutionProvider', 'CPUExecutionProvider'])
app_model.prepare(ctx_id=0, det_size=(640, 640))
qr_detector = cv2.QRCodeDetector()

def get_db_connection():
    return mysql.connector.connect(**db_config)

# Use relative paths or environment variables to avoid hardcoding C:/ paths
QR_STORAGE_PATH = os.path.join(os.getcwd(), "public", "qrcodes")
if not os.path.exists(QR_STORAGE_PATH):
    os.makedirs(QR_STORAGE_PATH)

# @app.route('/generate', methods=['GET'])
# def generate_qr():
#     random_key = str(uuid.uuid4())[:8]
#     filename = f"qr_{random_key}.png"
#     filepath = os.path.join(QR_STORAGE_PATH, filename)

#     img = qrcode.make(random_key)
#     img.save(filepath)

#     return jsonify({
#         "status": "success",
#         "key": random_key,
#         "qr_path": f"qrcodes/{filename}"
#     })

# gres test
@app.route('/generate-qr', methods=['POST'])
def generate_qr():
    data = request.json

    # Pastikan mengambil locker_session_id
    ls_id = str(data.get('locker_session_id'))
    item_detail = str(data.get('item_detail') or "barang").replace(" ", "_")
    key_data = data.get('key')

    project_root = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../../"))

    # Nama folder tetap locker_{id_session}
    folder_name = f"locker_{ls_id}"
    target_folder = os.path.join(project_root, "public", "images", "qr", folder_name)


    os.makedirs(target_folder, exist_ok=True)

    # Nama file unik per item detail
    file_name = f"qr_{item_detail}.png"
    file_path = os.path.join(target_folder, file_name)

    img = qrcode.make(key_data)
    img.save(file_path)

    return jsonify({
        "status": "success",
        "relative_path": f"images/qr/{folder_name}/{file_name}"
    })



@app.route('/recognize', methods=['POST'])
def recognize():
    if 'images' not in request.files:
        return jsonify({"error": "No images uploaded"}), 400

    file = request.files['images']
    img = cv2.imdecode(np.frombuffer(file.read(), np.uint8), cv2.IMREAD_COLOR)

    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)

    try:
        # STEP A: ONLY DECODE QR
        qr_data, points, _ = qr_detector.detectAndDecode(img)
        if qr_data:
            return jsonify([{"type": "qr_raw", "key": qr_data}])

        # STEP B: FACE RECOGNITION FALLBACK
        # Threshold adjusted to 0.45 for better security on buffalo_l
        best_name, best_id, min_dist = "STRANGER", None, 0.45
        faces = app_model.get(img)

        if faces:
            target_emb = faces[0].normed_embedding
            cursor.execute("SELECT id, name, face_embedding FROM users WHERE face_embedding IS NOT NULL")
            for record in cursor.fetchall():
                db_emb = np.array(json.loads(record['face_embedding']))
                dist = cosine(target_emb, db_emb)
                if dist < min_dist:
                    min_dist, best_name, best_id = dist, record['name'], record['id']

        return jsonify([{"type": "face", "result": best_name, "user_id": best_id}])

    finally:
        cursor.close()
        conn.close()

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
