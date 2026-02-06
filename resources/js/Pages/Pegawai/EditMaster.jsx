import AdminLayout from '@/Layouts/AdminLayout'
import { useForm, router } from '@inertiajs/react'

export default function EditMaster({ pegawai }) {
    const { data, setData, put, processing, errors } = useForm({
        nik: pegawai.nik || '',
        nip: pegawai.nip || '',
        nama: pegawai.nama || '',
    })

    function submit(e) {
        e.preventDefault()
        put(`/pegawai/${pegawai.id}/update-master`)
    }

    return (
        <AdminLayout title="Edit Master Pegawai">
            <div className="box">
                <form className="box-body" onSubmit={submit}>
                    <div className="form-group">
                        <label>NIK</label>
                        <input
                            className="form-control"
                            value={data.nik}
                            onChange={(e) => setData('nik', e.target.value)}
                        />
                        {errors.nik && (
                            <span className="text-danger">{errors.nik}</span>
                        )}
                    </div>

                    <div className="form-group">
                        <label>NIP</label>
                        <input
                            className="form-control"
                            value={data.nip}
                            onChange={(e) => setData('nip', e.target.value)}
                        />
                        {errors.nip && (
                            <span className="text-danger">{errors.nip}</span>
                        )}
                    </div>

                    <div className="form-group">
                        <label>Nama</label>
                        <input
                            className="form-control"
                            value={data.nama}
                            onChange={(e) => setData('nama', e.target.value)}
                        />
                        {errors.nama && (
                            <span className="text-danger">{errors.nama}</span>
                        )}
                    </div>

                    <button
                        className="btn btn-primary"
                        disabled={processing}
                    >
                        Simpan
                    </button>

                    <button
                        type="button"
                        className="btn btn-default"
                        onClick={() => router.get('/pegawai')}
                        style={{ marginLeft: 8 }}
                    >
                        Batal
                    </button>
                </form>
            </div>
        </AdminLayout>
    )
}
