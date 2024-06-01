using System.Collections;
using TMPro; // Use this if you're using TextMeshPro fields.
using Unity.VisualScripting;
using UnityEngine;
using UnityEngine.Networking;
using UnityEngine.UI;

public class LoginToWordpress : MonoBehaviour
{
    public InputField usernameField; // Make sure these are TMP_InputField if using TextMeshPro.
    public InputField passwordField;
    public Button loginButton;
    public Toggle saveCredentialsToggle;
    public GameObject MenuManagmentChat;
    public GameObject MenuConnect;
    public GameObject MenueLogin;//
    private string loginURL = "https://ursite.com/login.php";

    private const string PrefKeyUsername = "Username";
    private const string PrefKeyPassword = "Password";
    private const string PrefKeySaveCredentials = "SaveCredentials";
    private const string PrefKeyToken = "UserToken"; // Key to save the JWT token

    void Start()
    {
        loginButton.onClick.AddListener(() =>
        {
            Login(usernameField.text, passwordField.text);
        });

        LoadCredentials();
    }

    public void Login(string username, string password)
    {
        if (saveCredentialsToggle.isOn)
        {
            SaveCredentials(username, password);
        }
        else
        {
            ClearCredentials();
        }
        StartCoroutine(LoginCoroutine(username, password));
    }

    private void SaveCredentials(string username, string password)
    {
        PlayerPrefs.SetString(PrefKeyUsername, username);
        PlayerPrefs.SetString(PrefKeyPassword, password);
        PlayerPrefs.SetInt(PrefKeySaveCredentials, saveCredentialsToggle.isOn ? 1 : 0);
        PlayerPrefs.Save();
    }

    private void ClearCredentials()
    {
        PlayerPrefs.DeleteKey(PrefKeyUsername);
        PlayerPrefs.DeleteKey(PrefKeyPassword);
        PlayerPrefs.SetInt(PrefKeySaveCredentials, 0);
        PlayerPrefs.Save();
    }

    private void LoadCredentials()
    {
        if (PlayerPrefs.HasKey(PrefKeySaveCredentials) && PlayerPrefs.GetInt(PrefKeySaveCredentials) == 1)
        {
            saveCredentialsToggle.isOn = true;
            usernameField.text = PlayerPrefs.GetString(PrefKeyUsername);
            passwordField.text = PlayerPrefs.GetString(PrefKeyPassword);
        }
        else
        {
            saveCredentialsToggle.isOn = false;
        }
    }
    IEnumerator LoginCoroutine(string username, string password)
    {
        WWWForm form = new WWWForm();
        form.AddField("username", username);
        form.AddField("password", password);

        using (UnityWebRequest www = UnityWebRequest.Post(loginURL, form))
        {
           Debug.Log(loginURL);
            yield return www.SendWebRequest();

            if (www.isNetworkError || www.isHttpError)
            {
                Debug.LogError(www.error);
            }
            else
            {
                string responseText = www.downloadHandler.text;
                LoginResponse response = JsonUtility.FromJson<LoginResponse>(responseText);
                if (response.status == "success")
                {
                    var token = response.token; // Extract the token
                    PlayerPrefs.SetString(PrefKeyToken, token); // Save the token
                    PlayerPrefs.Save();
                    Debug.Log("Token saved: " + token);
                    MenueLogin.SetActive(false);
                    MenuConnect.SetActive(true);
                    
                }
                else
                {
                    Debug.LogError("Login Failed: " + response.message);
                }
            }
        }
    }

    // Existing methods for SaveCredentials, ClearCredentials, and LoadCredentials remain unchanged...

    // Add a new method to clear the saved token
    void ClearToken()
    {
        PlayerPrefs.DeleteKey(PrefKeyToken);
        PlayerPrefs.Save();
    }

    // Example method for making an authenticated request using the saved token
    IEnumerator AuthenticatedVerifyTokenRequest(string url)
    {
        if (PlayerPrefs.HasKey(PrefKeyToken))
        {
            string token = PlayerPrefs.GetString(PrefKeyToken);
            UnityWebRequest www = UnityWebRequest.Get(url);
            www.SetRequestHeader("Authorization", "Bearer " + token);
            yield return www.SendWebRequest();

            if (www.isNetworkError || www.isHttpError)
            {
                Debug.LogError(www.error);
            }
            else
            {
                Debug.Log("Authenticated request successful: " + www.downloadHandler.text);
            }
        }
        else
        {
            Debug.LogError("No token saved. Please log in.");
        }
    }




    // Example method to make a request to a protected endpoint using the JWT
    public void MakeAuthenticatedRequest(string url)
    {
        StartCoroutine(AuthenticatedRequestCoroutine(url));
    }

    IEnumerator AuthenticatedRequestCoroutine(string url)
    {
        UnityWebRequest www = UnityWebRequest.Get(url);
        string token = PlayerPrefs.GetString(PrefKeyToken, "");

        if (!string.IsNullOrEmpty(token))
        {
            // Add the authorization header with the JWT
            www.SetRequestHeader("Authorization", "Bearer " + token);

            yield return www.SendWebRequest();

            if (www.isNetworkError || www.isHttpError)
            {
                // If there's an error, log it (401 might mean the token was invalid or expired)
                Debug.LogError(www.error);
            }
            else
            {
                // If the server's response is okay, handle the response data
                Debug.Log("Response from server: " + www.downloadHandler.text);
                // Here you can parse the response and proceed accordingly
            }
        }
        else
        {
            Debug.LogError("No token saved. Please log in first.");
        }
    }
}
