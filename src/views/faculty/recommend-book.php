<style>
.recommend-container {
    max-width: 500px;
    margin: 40px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(102,126,234,0.15);
    padding: 32px 24px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.recommend-container h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 24px;
    color: #667eea;
    text-align: center;
}
.recommend-form label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    display: block;
}
.recommend-form input, .recommend-form textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    margin-bottom: 18px;
    background: #f9fafb;
    transition: border-color 0.3s;
}
.recommend-form input:focus, .recommend-form textarea:focus {
    border-color: #667eea;
    outline: none;
    background: #fff;
}
.recommend-form textarea {
    min-height: 80px;
    resize: vertical;
}
.recommend-form button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(102,126,234,0.15);
    transition: background 0.3s, transform 0.2s;
}
.recommend-form button:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
}
</style>
<div class="recommend-container">
    <h2>Recommend a Book</h2>
    <form class="recommend-form" method="POST" action="/faculty/recommend-book">
        <label>Title:
            <input type="text" name="title" required>
        </label>
        <label>Author:
            <input type="text" name="author" required>
        </label>
        <label>ISBN:
            <input type="text" name="isbn">
        </label>
        <label>Publisher:
            <input type="text" name="publisher">
        </label>
        <label>Edition:
            <input type="text" name="edition">
        </label>
        <label>Year:
            <input type="number" name="year">
        </label>
        <label>Subject Category:
            <input type="text" name="subject_category">
        </label>
        <label>Justification:
            <textarea name="justification" required></textarea>
        </label>
        <label>Estimated Price:
            <input type="number" step="0.01" name="estimated_price">
        </label>
        <button type="submit">Submit Recommendation</button>
    </form>
</div>