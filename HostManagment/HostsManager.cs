using System.Collections;
using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Networking;

public class HostsManager : MonoBehaviour
{
    private string apiUrl = "http://yourwebsite.com/wp-json/api/v1/online-hosts/";

    void Start()
    {
        StartCoroutine(GetOnlineHosts());
    }

    IEnumerator GetOnlineHosts()
    {
        using (UnityWebRequest webRequest = UnityWebRequest.Get(apiUrl))
        {
            // Request and wait for the desired page.
            yield return webRequest.SendWebRequest();

            if (webRequest.result != UnityWebRequest.Result.Success)
            {
                Debug.LogError("Error: " + webRequest.error);
            }
            else
            {
                ProcessHostsData(webRequest.downloadHandler.text);
            }
        }
    }

    void ProcessHostsData(string json)
    {
        List<string> hosts = JsonUtility.FromJson<List<string>>(json);
        foreach (string host in hosts)
        {
            Debug.Log("Online host: " + host);
        }
    }
}
