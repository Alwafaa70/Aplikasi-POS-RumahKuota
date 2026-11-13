// public/js/pwa.js

// Registrasi Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('Service Worker registered', reg))
            .catch(err => console.error('Service Worker registration failed', err));
    });
}

// Inisialisasi IndexedDB
const DB_NAME = 'pos-db';
const DB_VERSION = 1;
const STORE_NAME = 'offline-transactions';
let db;

const request = indexedDB.open(DB_NAME, DB_VERSION);

request.onupgradeneeded = (event) => {
    db = event.target.result;
    if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
    }
};

request.onsuccess = (event) => {
    db = event.target.result;
    // Coba sync saat DB siap (siapa tahu baru online)
    if (navigator.onLine) {
        syncTransactions();
    }
};

request.onerror = (event) => {
    console.error('IndexedDB error:', event.target.errorCode);
};

// Menyimpan Transaksi Offline
window.saveOfflineTransaction = function(payload) {
    return new Promise((resolve, reject) => {
        if (!db) {
            reject('Database belum siap');
            return;
        }
        const transaction = db.transaction([STORE_NAME], 'readwrite');
        const store = transaction.objectStore(STORE_NAME);
        
        const offlineData = {
            payload: payload,
            timestamp: new Date().toISOString()
        };
        
        const req = store.add(offlineData);
        req.onsuccess = () => resolve('Tersimpan di IndexedDB');
        req.onerror = () => reject('Gagal menyimpan ke IndexedDB');
    });
};

// Sinkronisasi Transaksi Offline
window.syncTransactions = function() {
    if (!db) return;
    
    const transaction = db.transaction([STORE_NAME], 'readonly');
    const store = transaction.objectStore(STORE_NAME);
    const getAllReq = store.getAll();
    
    getAllReq.onsuccess = async (event) => {
        const transactions = event.target.result;
        if (transactions.length === 0) return;
        
        console.log(`Menyinkronkan ${transactions.length} transaksi...`);
        
        try {
            // Kita harus mengambil _token dari DOM jika ada, atau mengambil dari transaksi.
            // Transaksi offline payload sudah menyimpan _token saat itu.
            // Panggil API /kasir/sync-transactions (Endpoint Batch)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const response = await fetch('/kasir/sync-transactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(transactions)
            });
            
            if (response.ok) {
                // Hapus data dari IndexedDB karena sudah berhasil
                const deleteTx = db.transaction([STORE_NAME], 'readwrite');
                const deleteStore = deleteTx.objectStore(STORE_NAME);
                deleteStore.clear();
                
                // Tampilkan notifikasi (opsional, bisa di-hook ke Alpine jika perlu)
                alert('Sinkronisasi transaksi offline berhasil!');
            }
        } catch (error) {
            console.error('Sinkronisasi gagal, akan dicoba lagi nanti', error);
        }
    };
};

// Deteksi saat kembali Online
window.addEventListener('online', () => {
    console.log('Koneksi internet kembali. Mulai sinkronisasi...');
    syncTransactions();
});
