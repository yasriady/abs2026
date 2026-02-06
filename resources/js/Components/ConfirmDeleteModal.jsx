import { useState } from 'react'

export default function ConfirmDeleteModal({
    show,
    title = 'Konfirmasi Hapus',
    message,
    onCancel,
    onConfirm,
}) {
    const [confirmText, setConfirmText] = useState('')

    if (!show) return null

    const canDelete = confirmText === 'delete'

    return (
        <>
            <div className="modal fade in" style={{ display: 'block' }}>
                <div className="modal-dialog modal-sm">
                    <div className="modal-content">

                        <div className="modal-header bg-red">
                            <button
                                type="button"
                                className="close"
                                onClick={() => {
                                    setConfirmText('')
                                    onCancel()
                                }}
                            >
                                ×
                            </button>
                            <h4 className="modal-title">{title}</h4>
                        </div>

                        <div className="modal-body">
                            {message}

                            <hr />

                            <p className="text-danger">
                                Ketik <strong>delete</strong> untuk melanjutkan
                            </p>

                            <input
                                type="text"
                                className="form-control"
                                placeholder='Ketik "delete"'
                                value={confirmText}
                                onChange={e => setConfirmText(e.target.value)}
                            />
                        </div>

                        <div className="modal-footer">
                            <button
                                className="btn btn-default"
                                onClick={() => {
                                    setConfirmText('')
                                    onCancel()
                                }}
                            >
                                Batal
                            </button>

                            <button
                                className="btn btn-danger"
                                disabled={!canDelete}
                                onClick={() => {
                                    onConfirm()
                                    setConfirmText('')
                                }}
                            >
                                Hapus
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <div className="modal-backdrop fade in"></div>
        </>
    )
}
