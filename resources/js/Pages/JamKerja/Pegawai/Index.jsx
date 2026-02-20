import AdminLayout from '@/Layouts/AdminLayout'
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal'
import { router, useForm } from '@inertiajs/react'
import { useMemo, useState } from 'react'

function toHm(value) {
  if (!value) return ''
  return String(value).slice(0, 5)
}

export default function Index({ jadwals, filters, pegawaiHints }) {
  const [modal, setModal] = useState(false)
  const [edit, setEdit] = useState(null)
  const [deleteTarget, setDeleteTarget] = useState(null)

  const [search, setSearch] = useState(filters.search || '')
  const [filterDate, setFilterDate] = useState(filters.date || '')

  const pegawaiMap = useMemo(() => {
    const map = {}
    ;(pegawaiHints || []).forEach((p) => {
      map[p.nik] = p
    })
    return map
  }, [pegawaiHints])

  const { data, setData, post, put, reset, errors, processing } = useForm({
    nik: '',
    date: '',
    jam_masuk: '',
    jam_pulang: '',
  })

  function applyFilters() {
    router.get(
      '/jam-kerja/pegawai',
      {
        search,
        date: filterDate,
      },
      { preserveState: true, replace: true }
    )
  }

  function resetFilters() {
    setSearch('')
    setFilterDate('')
    router.get('/jam-kerja/pegawai', {}, { preserveState: true, replace: true })
  }

  function openCreate() {
    setEdit(null)
    reset()
    setData({
      nik: '',
      date: '',
      jam_masuk: '',
      jam_pulang: '',
    })
    setModal(true)
  }

  function openEdit(item) {
    setEdit(item)
    setData({
      nik: item.nik,
      date: item.date,
      jam_masuk: toHm(item.jam_masuk),
      jam_pulang: toHm(item.jam_pulang),
    })
    setModal(true)
  }

  function closeModal() {
    setModal(false)
    setEdit(null)
    reset()
  }

  function submit(e) {
    e.preventDefault()

    if (edit) {
      put(`/jam-kerja/pegawai/${edit.id}`, {
        preserveScroll: true,
        onSuccess: closeModal,
      })
      return
    }

    post('/jam-kerja/pegawai', {
      preserveScroll: true,
      onSuccess: closeModal,
    })
  }

  return (
    <AdminLayout title="Jadwal Pegawai">
      <div className="box">
        <div className="box-header">
          <button className="btn btn-primary" onClick={openCreate}>
            Tambah Jadwal Pegawai
          </button>
        </div>

        <div className="box-body">
          <div className="row">
            <div className="col-sm-3">
              <label>Tanggal</label>
              <input
                type="date"
                className="form-control"
                value={filterDate}
                onChange={(e) => setFilterDate(e.target.value)}
              />
            </div>
            <div className="col-sm-5">
              <label>Search</label>
              <input
                className="form-control"
                placeholder="Cari NIK / Nama / NIP ..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>
            <div className="col-sm-4" style={{ paddingTop: '25px' }}>
              <button className="btn btn-primary btn-sm" onClick={applyFilters}>
                Terapkan
              </button>
              <button
                className="btn btn-default btn-sm"
                style={{ marginLeft: '6px' }}
                onClick={resetFilters}
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <div className="box-body table-responsive">
          <table className="table table-bordered table-striped">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Tanggal</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>NIP</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              {jadwals.data.map((item, i) => (
                <tr key={item.id}>
                  <td>{(jadwals.from || 1) + i}</td>
                  <td>{item.date}</td>
                  <td>{item.nik}</td>
                  <td>{item.pegawai_nama || '-'}</td>
                  <td>{item.pegawai_nip || '-'}</td>
                  <td>{toHm(item.jam_masuk)}</td>
                  <td>{toHm(item.jam_pulang)}</td>
                  <td>
                    <button
                      className="btn btn-xs btn-warning"
                      onClick={() => openEdit(item)}
                    >
                      Edit
                    </button>
                    <button
                      className="btn btn-xs btn-danger"
                      onClick={() => setDeleteTarget(item)}
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              ))}

              {jadwals.data.length === 0 && (
                <tr>
                  <td colSpan="8" className="text-center text-muted">
                    Belum ada data jadwal pegawai
                  </td>
                </tr>
              )}
            </tbody>
          </table>

          <div className="text-center">
            {jadwals.links.map((link, i) => (
              <button
                key={i}
                disabled={!link.url}
                className={`btn btn-xs ${link.active ? 'btn-primary' : 'btn-default'}`}
                onClick={() => router.get(link.url)}
                dangerouslySetInnerHTML={{ __html: link.label }}
              />
            ))}
          </div>
        </div>
      </div>

      {modal && (
        <>
          <div className="modal fade in" style={{ display: 'block' }}>
            <div className="modal-dialog">
              <form className="modal-content" onSubmit={submit}>
                <div className="modal-header">
                  <button type="button" className="close" onClick={closeModal}>
                    x
                  </button>
                  <h4>{edit ? 'Edit' : 'Tambah'} Jadwal Pegawai</h4>
                </div>

                <div className="modal-body">
                  <div className="form-group">
                    <label>NIK</label>
                    <input
                      className="form-control"
                      list="pegawai-hints"
                      placeholder="Ketik NIK"
                      value={data.nik}
                      onChange={(e) => setData('nik', e.target.value)}
                    />
                    <datalist id="pegawai-hints">
                      {(pegawaiHints || []).map((p) => (
                        <option key={p.nik} value={p.nik}>
                          {p.nama} {p.nip ? `- ${p.nip}` : ''}
                        </option>
                      ))}
                    </datalist>
                    {errors.nik && <div className="text-danger">{errors.nik}</div>}
                    {data.nik && pegawaiMap[data.nik] && (
                      <small className="text-muted">
                        {pegawaiMap[data.nik].nama}
                        {pegawaiMap[data.nik].nip ? ` (${pegawaiMap[data.nik].nip})` : ''}
                      </small>
                    )}
                  </div>

                  <div className="form-group">
                    <label>Tanggal</label>
                    <input
                      type="date"
                      className="form-control"
                      value={data.date}
                      onChange={(e) => setData('date', e.target.value)}
                    />
                    {errors.date && <div className="text-danger">{errors.date}</div>}
                  </div>

                  <div className="row">
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Jam Masuk</label>
                        <input
                          type="time"
                          className="form-control"
                          value={data.jam_masuk}
                          onChange={(e) => setData('jam_masuk', e.target.value)}
                        />
                        {errors.jam_masuk && (
                          <div className="text-danger">{errors.jam_masuk}</div>
                        )}
                      </div>
                    </div>
                    <div className="col-sm-6">
                      <div className="form-group">
                        <label>Jam Pulang</label>
                        <input
                          type="time"
                          className="form-control"
                          value={data.jam_pulang}
                          onChange={(e) => setData('jam_pulang', e.target.value)}
                        />
                        {errors.jam_pulang && (
                          <div className="text-danger">{errors.jam_pulang}</div>
                        )}
                      </div>
                    </div>
                  </div>
                </div>

                <div className="modal-footer">
                  <button type="button" className="btn btn-default" onClick={closeModal}>
                    Batal
                  </button>
                  <button className="btn btn-primary" disabled={processing}>
                    {processing ? 'Menyimpan...' : 'Simpan'}
                  </button>
                </div>
              </form>
            </div>
          </div>
          <div className="modal-backdrop fade in"></div>
        </>
      )}

      <ConfirmDeleteModal
        show={!!deleteTarget}
        title="Konfirmasi Hapus Jadwal Pegawai"
        message={
          <p>
            Yakin menghapus jadwal:
            <br />
            <strong>{deleteTarget?.pegawai_nama || deleteTarget?.nik}</strong>
            {' | '}
            <strong>{deleteTarget?.date}</strong>
          </p>
        }
        onCancel={() => setDeleteTarget(null)}
        onConfirm={() => {
          if (!deleteTarget) return
          router.delete(`/jam-kerja/pegawai/${deleteTarget.id}`, {
            preserveScroll: true,
            onSuccess: () => setDeleteTarget(null),
          })
        }}
      />
    </AdminLayout>
  )
}
