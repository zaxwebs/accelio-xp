const API_BASE = '/api';

export async function listDocuments() {
    const res = await fetch(`${API_BASE}/documents`);
    return res.json();
}

export async function getDocument(id) {
    const res = await fetch(`${API_BASE}/documents/${id}`);
    return res.json();
}

export async function createDocument(data) {
    const res = await fetch(`${API_BASE}/documents`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    return res.json();
}

export async function updateDocument(id, data) {
    const res = await fetch(`${API_BASE}/documents/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    return res.json();
}

export async function deleteDocument(id) {
    const res = await fetch(`${API_BASE}/documents/${id}`, {
        method: 'DELETE',
    });
    return res.json();
}

export async function uploadImage(name, base64Data, documentId = null) {
    const body = { name, image: base64Data };
    if (documentId) body.document_id = documentId;

    const res = await fetch(`${API_BASE}/documents/upload`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
    });
    return res.json();
}

export function getImageUrl(id) {
    return `${API_BASE}/documents/${id}/image`;
}
