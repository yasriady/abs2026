import AdminLayout from '@/Layouts/AdminLayout'
import { useForm } from '@inertiajs/react'

export default function Edit({ pegawai, history, units, subUnits }) {
    const { data, setData, post, processing, errors } = useForm({
        _method: 'PUT', // ⬅️ PENTING
        nik: pegawai.nik,
        nip: pegawai.nip || '',
        nama: pegawai.nama,

        status_kepegawaian: history?.status_kepegawaian || '',
        id_unit: history?.id_unit || '',
        id_sub_unit: history?.id_sub_unit || '',
        begin_date: history?.begin_date || '',
        end_date: history?.end_date || '',
        lokasi_kerja: history?.lokasi_kerja || '',

        foto: null,
    })
    
    function submit(e) {
        e.preventDefault()
        post(`/v1/pegawai/${pegawai.id}`, {
            forceFormData: true,
        })
    }



    return (
        <AdminLayout title="Edit Pegawai">
            <form onSubmit={submit} className="box box-primary">
                <div className="box-body">

                    <h4>Identitas Pegawai</h4>

                    <div className="row">
                        <div className="col-md-4">
                            <label>NIK</label>
                            <input
                                className="form-control"
                                value={data.nik}
                                readOnly
                            />

                            {/* DOUBLE SAFETY */}
                            <input type="hidden" value={data.nik} />

                            {errors.nik && <small className="text-danger">{errors.nik}</small>}
                        </div>


                        <div className="col-md-4">
                            <label>NIP</label>
                            <input className="form-control" value={data.nip}
                                onChange={e => setData('nip', e.target.value)} />
                        </div>

                        <div className="col-md-4">
                            <label>Nama</label>
                            <input className="form-control" value={data.nama}
                                onChange={e => setData('nama', e.target.value)} />
                        </div>

                        <div className="col-md-4">
                            <label>Foto Pegawai</label>

                            {/* PREVIEW FOTO LAMA */}
                            <div style={{ marginBottom: 6 }}>
                                <img
                                    src={
                                        pegawai.foto
                                            ? `/storage/${pegawai.foto}`
                                            : '/images/default-user.png'
                                    }
                                    alt="foto"
                                    style={{
                                        width: 80,
                                        height: 100,
                                        objectFit: 'cover',
                                        border: '1px solid #ddd',
                                    }}
                                />
                            </div>

                            {/* INPUT FILE */}
                            <input
                                type="file"
                                className="form-control"
                                accept="image/*"
                                onChange={(e) => setData('foto', e.target.files[0])}
                            />

                            {errors.foto && (
                                <small className="text-danger">{errors.foto}</small>
                            )}
                        </div>

                    </div>

                    <hr />

                    <h4>Histori Aktif</h4>

                    <div className="row">
                        <div className="col-md-3">
                            <label>Status</label>
                            <select className="form-control"
                                value={data.status_kepegawaian}
                                onChange={e => setData('status_kepegawaian', e.target.value)}>
                                <option value="">- pilih -</option>
                                <option value="pns">PNS</option>
                                <option value="pppk">PPPK</option>
                                <option value="pppk-pw">PPPK-PW</option>
                                <option value="thl">THL</option>
                                <option value="nib">NIB</option>
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Unit</label>
                            <select className="form-control"
                                value={data.id_unit}
                                onChange={e => setData('id_unit', e.target.value)}>
                                <option value="">- pilih -</option>
                                {units.map(u => (
                                    <option key={u.id} value={u.id}>{u.unit}</option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Sub Unit</label>
                            <select className="form-control"
                                value={data.id_sub_unit}
                                onChange={e => setData('id_sub_unit', e.target.value)}>
                                <option value="">-</option>
                                {subUnits.map(s => (
                                    <option key={s.id} value={s.id}>{s.sub_unit}</option>
                                ))}
                            </select>
                        </div>

                        <div className="col-md-3">
                            <label>Lokasi Kerja</label>
                            <input className="form-control"
                                value={data.lokasi_kerja}
                                onChange={e => setData('lokasi_kerja', e.target.value)} />
                        </div>
                    </div>

                    <div className="row" style={{ marginTop: 10 }}>
                        <div className="col-md-3">
                            <label>Tgl Mulai</label>
                            <input type="date" className="form-control"
                                value={data.begin_date}
                                onChange={e => setData('begin_date', e.target.value)} />
                        </div>

                        <div className="col-md-3">
                            <label>Tgl Selesai</label>
                            <input type="date" className="form-control"
                                value={data.end_date}
                                onChange={e => setData('end_date', e.target.value)} />
                        </div>
                    </div>

                </div>

                <div className="box-footer">
                    <button className="btn btn-primary" disabled={processing}>
                        Simpan
                    </button>
                    <a href="/v1/pegawai" className="btn btn-default">Batal</a>
                </div>
            </form>
        </AdminLayout>
    )
}
