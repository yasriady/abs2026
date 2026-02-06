import AdminLayout from '@/Layouts/AdminLayout'
import { useForm, router, usePage } from '@inertiajs/react'

export default function EditHistoriRaw({ pegawai, history }) {
    const { auth } = usePage().props

    const { data, setData, put, processing, errors } = useForm({
        status_kepegawaian: history.status_kepegawaian || '',
        id_unit: history.id_unit || '',
        id_sub_unit: history.id_sub_unit || '',
        id_struktur_organisasi: history.id_struktur_organisasi || '',
        begin_date: history.begin_date || '',
        end_date: history.end_date || '',
        is_active: !!history.is_active,
        lokasi_kerja: history.lokasi_kerja || '',
        order: history.order || 0,
    })

    function submit(e) {
        e.preventDefault()
        put(`/pegawai/${pegawai.id}/histori/${history.id}/raw-update`)
    }

    return (
        <AdminLayout title="RAW Edit Histori Pegawai">
            <div className="box">
                <div className="box-body">
                    <div className="alert alert-warning">
                        <strong>PERINGATAN:</strong> Halaman ini mengubah data histori secara langsung.
                        Gunakan hanya untuk perbaikan data / migrasi.
                    </div>

                    <h4>{pegawai.nama}</h4>
                    <div className="text-muted">
                        NIK: {pegawai.nik} | NIP: {pegawai.nip || '-'}
                    </div>

                    <form onSubmit={submit}>
                        <div className="row">
                            <div className="col-md-3">
                                <label>Status</label>
                                <select
                                    className="form-control"
                                    value={data.status_kepegawaian}
                                    onChange={(e) =>
                                        setData('status_kepegawaian', e.target.value)
                                    }
                                >
                                    <option value="">- Pilih -</option>
                                    <option value="asn">ASN</option>
                                    <option value="pns">PNS</option>
                                    <option value="pppk">PPPK</option>
                                    <option value="pppk-pw">PPPK-PW</option>
                                    <option value="thl">THL</option>
                                    <option value="nib">NIB</option>
                                </select>
                                {errors.status_kepegawaian && (
                                    <span className="text-danger">
                                        {errors.status_kepegawaian}
                                    </span>
                                )}
                            </div>

                            <div className="col-md-3">
                                <label>Unit ID</label>
                                <input
                                    className="form-control"
                                    value={data.id_unit}
                                    onChange={(e) =>
                                        setData('id_unit', e.target.value)
                                    }
                                />
                            </div>

                            <div className="col-md-3">
                                <label>Sub Unit ID</label>
                                <input
                                    className="form-control"
                                    value={data.id_sub_unit}
                                    onChange={(e) =>
                                        setData('id_sub_unit', e.target.value)
                                    }
                                />
                            </div>

                            <div className="col-md-3">
                                <label>Struktur ID</label>
                                <input
                                    className="form-control"
                                    value={data.id_struktur_organisasi}
                                    onChange={(e) =>
                                        setData(
                                            'id_struktur_organisasi',
                                            e.target.value
                                        )
                                    }
                                />
                            </div>
                        </div>

                        <div className="row" style={{ marginTop: 10 }}>
                            <div className="col-md-3">
                                <label>Begin Date</label>
                                <input
                                    type="date"
                                    className="form-control"
                                    value={data.begin_date}
                                    onChange={(e) =>
                                        setData('begin_date', e.target.value)
                                    }
                                />
                            </div>

                            <div className="col-md-3">
                                <label>End Date</label>
                                <input
                                    type="date"
                                    className="form-control"
                                    value={data.end_date || ''}
                                    onChange={(e) =>
                                        setData('end_date', e.target.value)
                                    }
                                />
                            </div>

                            <div className="col-md-2">
                                <label>Aktif</label>
                                <div className="checkbox">
                                    <label>
                                        <input
                                            type="checkbox"
                                            checked={data.is_active}
                                            onChange={(e) =>
                                                setData(
                                                    'is_active',
                                                    e.target.checked
                                                )
                                            }
                                        />{' '}
                                        Is Active
                                    </label>
                                </div>
                            </div>

                            <div className="col-md-2">
                                <label>Order</label>
                                <input
                                    type="number"
                                    className="form-control"
                                    value={data.order}
                                    onChange={(e) =>
                                        setData('order', e.target.value)
                                    }
                                />
                            </div>
                        </div>

                        <div className="form-group" style={{ marginTop: 10 }}>
                            <label>Lokasi Kerja</label>
                            <input
                                className="form-control"
                                value={data.lokasi_kerja}
                                onChange={(e) =>
                                    setData('lokasi_kerja', e.target.value)
                                }
                            />
                        </div>

                        <button
                            className="btn btn-danger"
                            disabled={processing}
                        >
                            Simpan RAW
                        </button>

                        <button
                            type="button"
                            className="btn btn-default"
                            style={{ marginLeft: 8 }}
                            onClick={() =>
                                router.get(`/pegawai/${pegawai.id}/histori`)
                            }
                        >
                            Batal
                        </button>
                    </form>
                </div>
            </div>
        </AdminLayout>
    )
}
