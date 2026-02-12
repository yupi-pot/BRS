import { useState, useEffect, useRef } from 'react'
import './App.css'

const API_URL = 'http://localhost:8080/api/notes'

// Компонент модального окна
function Modal({ isOpen, onClose, onConfirm, title, message, type = 'alert' }) {
  if (!isOpen) return null

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal-content" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h3>{title}</h3>
          <button className="modal-close" onClick={onClose}>&times;</button>
        </div>
        <div className="modal-body">
          <p>{message}</p>
        </div>
        <div className="modal-footer">
          {type === 'confirm' ? (
            <>
              <button className="btn-secondary" onClick={onClose}>Отмена</button>
              <button className="btn-delete" onClick={onConfirm}>Удалить</button>
            </>
          ) : (
            <button className="btn-primary" onClick={onClose}>OK</button>
          )}
        </div>
      </div>
    </div>
  )
}

function App() {
  // Ref для формы (для скролла при редактировании)
  const formRef = useRef(null)

  // Состояние приложения
  const [notes, setNotes] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  // Состояние формы
  const [isEditing, setIsEditing] = useState(false)
  const [currentNote, setCurrentNote] = useState({ id: null, title: '', content: '' })

  // Состояние модального окна
  const [modal, setModal] = useState({
    isOpen: false,
    title: '',
    message: '',
    type: 'alert',
    onConfirm: null
  })

  // Загрузка заметок при монтировании компонента
  useEffect(() => {
    fetchNotes()
  }, [])

  // Получить все заметки
  const fetchNotes = async () => {
    try {
      setLoading(true)
      const response = await fetch(API_URL)
      const data = await response.json()
      setNotes(data.data || [])
      setError(null)
    } catch (err) {
      setError('Ошибка загрузки заметок')
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  // Создать новую заметку
  const createNote = async (note) => {
    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(note)
      })
      const data = await response.json()
      if (data.success) {
        setNotes([data.data, ...notes])
        resetForm()
      }
    } catch (err) {
      setError('Ошибка создания заметки')
      console.error(err)
    }
  }

  // Обновить заметку
  const updateNote = async (id, note) => {
    try {
      const response = await fetch(`${API_URL}/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(note)
      })
      const data = await response.json()
      if (data.success) {
        setNotes(notes.map(n => n.id === id ? data.data : n))
        resetForm()
      }
    } catch (err) {
      setError('Ошибка обновления заметки')
      console.error(err)
    }
  }

  // Удалить заметку
  const deleteNote = async (id) => {
    setModal({
      isOpen: true,
      title: 'Подтверждение удаления',
      message: 'Вы уверены, что хотите удалить эту заметку? Это действие нельзя отменить.',
      type: 'confirm',
      onConfirm: async () => {
        setModal({ ...modal, isOpen: false })
        try {
          const response = await fetch(`${API_URL}/${id}`, {
            method: 'DELETE'
          })
          const data = await response.json()
          if (data.success) {
            setNotes(notes.filter(n => n.id !== id))
          }
        } catch (err) {
          setError('Ошибка удаления заметки')
          console.error(err)
        }
      }
    })
  }

  // Начать редактирование
  const startEdit = (note) => {
    setIsEditing(true)
    setCurrentNote(note)
    // Скроллим к форме плавно
    setTimeout(() => {
      formRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' })
    }, 100)
  }

  // Сбросить форму
  const resetForm = () => {
    setIsEditing(false)
    setCurrentNote({ id: null, title: '', content: '' })
  }

  // Обработка отправки формы
  const handleSubmit = (e) => {
    e.preventDefault()
    if (!currentNote.title || !currentNote.content) {
      setModal({
        isOpen: true,
        title: 'Ошибка валидации',
        message: 'Пожалуйста, заполните все поля: заголовок и содержимое заметки.',
        type: 'alert',
        onConfirm: null
      })
      return
    }

    if (isEditing) {
      updateNote(currentNote.id, { title: currentNote.title, content: currentNote.content })
    } else {
      createNote({ title: currentNote.title, content: currentNote.content })
    }
  }

  if (loading) return <div className="container"><h2>Загрузка...</h2></div>

  return (
    <div className="container">
      <header>
        <h1>📝 Мои Заметки</h1>
        <p>Простое приложение для управления заметками</p>
      </header>

      {error && <div className="error">{error}</div>}

      {/* Форма создания/редактирования */}
      <div className="form-container" ref={formRef}>
        <h2>{isEditing ? 'Редактировать заметку' : 'Создать новую заметку'}</h2>
        <form onSubmit={handleSubmit}>
          <input
            type="text"
            placeholder="Заголовок"
            value={currentNote.title}
            onChange={(e) => setCurrentNote({ ...currentNote, title: e.target.value })}
            maxLength="255"
          />
          <textarea
            placeholder="Содержимое заметки"
            value={currentNote.content}
            onChange={(e) => setCurrentNote({ ...currentNote, content: e.target.value })}
            rows="4"
          />
          <div className="form-buttons">
            <button type="submit" className="btn-primary">
              {isEditing ? 'Сохранить' : 'Создать'}
            </button>
            {isEditing && (
              <button type="button" onClick={resetForm} className="btn-secondary">
                Отмена
              </button>
            )}
          </div>
        </form>
      </div>

      {/* Список заметок */}
      <div className="notes-list">
        <h2>Все заметки ({notes.length})</h2>
        {notes.length === 0 ? (
          <p className="empty-state">Пока нет заметок. Создайте первую!</p>
        ) : (
          notes.map(note => (
            <div key={note.id} className="note-card">
              <h3>{note.title}</h3>
              <p>{note.content}</p>
              <div className="note-meta">
                <small>Создано: {new Date(note.created_at).toLocaleString('ru-RU')}</small>
              </div>
              <div className="note-actions">
                <button onClick={() => startEdit(note)} className="btn-edit">
                  Редактировать
                </button>
                <button onClick={() => deleteNote(note.id)} className="btn-delete">
                  Удалить
                </button>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Модальное окно */}
      <Modal
        isOpen={modal.isOpen}
        onClose={() => setModal({ ...modal, isOpen: false })}
        onConfirm={modal.onConfirm}
        title={modal.title}
        message={modal.message}
        type={modal.type}
      />
    </div>
  )
}

export default App
